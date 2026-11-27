<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContactDuplicateService
{
    /**
     * Create a contact and update duplicate indicators
     * on the new contact and any matching contacts.
     */
    public function createContact(array $data): Contact
    {
        return DB::transaction(function () use ($data) {

            $data = array_merge([
                'broker_status' => 'active',
                'email_status' => 'unknown',
                'subscription_status' => 'subscribed',
                'duplicate_status' => 'N',
                'phone_duplicate_status' => 'N',
                'possible_duplicate' => false,
                'duplicate_notes' => null,
            ], $data);

            $data = $this->prepareContactData($data);

            $contact = Contact::create($data);

            $affectedIds = $this->matchingContactIds($contact);
            $affectedIds->push($contact->id);

            $this->recalculateDuplicates($affectedIds);

            return $contact->fresh();
        });
    }

    /**
     * Update a contact and recalculate duplicate indicators
     * for contacts matching either its old or new information.
     */
    public function updateContact(Contact $contact, array $data): Contact
    {
        return DB::transaction(function () use ($contact, $data) {

            $contact->refresh();

            // Find contacts matching the old information.
            $oldMatchingIds = $this->matchingContactIds($contact);

            // Update contact information.
            $data = $this->prepareContactData($data);
            $contact->update($data);

            $contact->refresh();

            // Find contacts matching the new information.
            $newMatchingIds = $this->matchingContactIds($contact);

            // Recalculate the edited contact and all affected contacts.
            $affectedIds = $oldMatchingIds
                ->merge($newMatchingIds)
                ->push($contact->id)
                ->unique()
                ->values();

            $this->recalculateDuplicates($affectedIds);

            return $contact->fresh();
        });
    }

    /**
     * Normalize submitted contact information.
     */
    private function prepareContactData(array $data): array
    {
        if (array_key_exists('email', $data)) {
            $email = strtolower(trim($data['email'] ?? ''));

            $data['email'] = $email !== '' ? $email : null;
        }

        if (array_key_exists('cell_phone', $data)) {
            $phone = preg_replace('/\D/', '', $data['cell_phone'] ?? '');

            // Remove the North American country code if already included.
            if (strlen($phone) === 11 && str_starts_with($phone, '1')) {
                $phone = substr($phone, 1);
            }

            // Save in standard North American format.
            if (strlen($phone) === 10) {
                $data['cell_phone'] = '+1 ' .
                    substr($phone, 0, 3) . '-' .
                    substr($phone, 3, 3) . '-' .
                    substr($phone, 6, 4);
            } else {
                $data['cell_phone'] = null;
            }
        }

        return $data;
    }

    /**
     * Find all active contacts matching any duplicate criterion.
     */
    private function matchingContactIds(Contact $contact): Collection
    {
        $email = strtolower(trim($contact->email ?? ''));
        $phone = $this->normalizePhone($contact->cell_phone);
        $contactName = trim($contact->contact ?? '');
        $brokerage = trim($contact->brokerage_name ?? '');
        $postalCode = trim($contact->zip_code ?? '');

        return Contact::query()
            ->where('id', '!=', $contact->id)
            ->where(function ($query) use (
                $email,
                $phone,
                $contactName,
                $brokerage,
                $postalCode
            ) {
                // Email match
                if ($email !== '') {
                    $query->orWhereRaw(
                        'LOWER(TRIM(email)) = ?',
                        [$email]
                    );
                }

                // Phone match
                if ($phone !== null) {
                    $query->orWhereRaw(
                        "REGEXP_REPLACE(COALESCE(cell_phone, ''), '[^0-9]', '') = ?",
                        [$phone]
                    );
                }

                // Contact name match
                if ($contactName !== '') {
                    $query->orWhereRaw(
                        'LOWER(TRIM(contact)) = ?',
                        [Str::lower($contactName)]
                    );
                }

                // Brokerage and postal code match
                if ($brokerage !== '' && $postalCode !== '') {
                    $query->orWhere(function ($branchQuery) use (
                        $brokerage,
                        $postalCode
                    ) {
                        $branchQuery
                            ->whereRaw(
                                'LOWER(TRIM(brokerage_name)) = ?',
                                [Str::lower($brokerage)]
                            )
                            ->whereRaw(
                                'LOWER(TRIM(zip_code)) = ?',
                                [Str::lower($postalCode)]
                            );
                    });
                }
            })
            ->pluck('id');
    }

    /**
     * Recalculate duplicate indicators for all affected contacts.
     */
    private function recalculateDuplicates(Collection $contactIds): void
    {
        foreach ($contactIds->unique() as $contactId) {

            $contact = Contact::find($contactId);

            if (!$contact) {
                continue;
            }

            $email = strtolower(trim($contact->email ?? ''));
            $phone = $this->normalizePhone($contact->cell_phone);
            $contactName = trim($contact->contact ?? '');
            $brokerage = trim($contact->brokerage_name ?? '');
            $postalCode = trim($contact->zip_code ?? '');

            // Reset indicators before recalculating.
            $contact->duplicate_status = 'N';
            $contact->phone_duplicate_status = 'N';
            $contact->possible_duplicate = false;

            $notes = [];

            // EMAIL DUPLICATE CHECK
            if ($email !== '') {
                $emailExists = Contact::query()
                    ->where('id', '!=', $contact->id)
                    ->whereRaw(
                        'LOWER(TRIM(email)) = ?',
                        [$email]
                    )
                    ->exists();

                if ($emailExists) {
                    $contact->duplicate_status = 'D';
                }
            }

            // PHONE DUPLICATE CHECK
            if ($phone !== null) {
                $phoneExists = Contact::query()
                    ->where('id', '!=', $contact->id)
                    ->whereNotNull('cell_phone')
                    ->whereRaw(
                        "REGEXP_REPLACE(cell_phone, '[^0-9]', '') = ?",
                        [$phone]
                    )
                    ->exists();

                if ($phoneExists) {
                    $contact->phone_duplicate_status = 'D';
                    $notes[] = 'Shared phone number';
                }
            }

            // CONTACT NAME DUPLICATE CHECK
            if ($contactName !== '') {
                $nameExists = Contact::query()
                    ->where('id', '!=', $contact->id)
                    ->whereRaw(
                        'LOWER(TRIM(contact)) = ?',
                        [Str::lower($contactName)]
                    )
                    ->exists();

                if ($nameExists) {
                    $contact->possible_duplicate = true;
                    $notes[] = 'Matching contact name';
                }
            }

            // BROKERAGE + POSTAL CODE CHECK
            if ($brokerage !== '' && $postalCode !== '') {
                $branchExists = Contact::query()
                    ->where('id', '!=', $contact->id)
                    ->whereRaw(
                        'LOWER(TRIM(brokerage_name)) = ?',
                        [Str::lower($brokerage)]
                    )
                    ->whereRaw(
                        'LOWER(TRIM(zip_code)) = ?',
                        [Str::lower($postalCode)]
                    )
                    ->exists();

                if ($branchExists) {
                    $contact->possible_duplicate = true;
                    $notes[] = 'Matching brokerage and postal code';
                }
            }

            // Rebuild notes so outdated notes are removed.
            $contact->duplicate_notes = $notes
                ? implode('; ', array_unique($notes))
                : null;

            $contact->save();
        }
    }

    /**
     * Normalize phone numbers to digits only.
     */
    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        return $digits !== '' ? $digits : null;
    }
}
