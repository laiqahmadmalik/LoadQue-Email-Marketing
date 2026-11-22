<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContactDuplicateService
{
    public function createContact(array $data): Contact
    {
        return DB::transaction(function () use ($data) {

            // Normalize email
            $email = isset($data['email'])
                ? strtolower(trim($data['email']))
                : null;

            $data['email'] = $email ?: null;

            // Default statuses for new contacts
            $data['broker_status'] = 'active';
            $data['email_status'] = 'unknown';
            $data['subscription_status'] = 'subscribed';

            $data['duplicate_status'] = 'N';
            $data['phone_duplicate_status'] = 'N';
            $data['possible_duplicate'] = false;
            $data['duplicate_notes'] = null;

            $notes = [];

            // EMAIL DUPLICATE CHECK
            if ($email) {
                $existingEmail = Contact::whereRaw(
                    'LOWER(TRIM(email)) = ?',
                    [$email]
                )->orderBy('id')->lockForUpdate()->first();

                if ($existingEmail) {
                    $data['duplicate_status'] = 'D';
                }
            }

            // PHONE DUPLICATE CHECK
            $phone = $data['cell_phone'] ?? null;
            $normalizedPhone = $this->normalizePhone($phone);

            if ($normalizedPhone) {
                $existingPhones = Contact::whereNotNull('cell_phone')
                    ->whereRaw(
                        "REGEXP_REPLACE(cell_phone, '[^0-9]', '') = ?",
                        [$normalizedPhone]
                    )
                    ->lockForUpdate()
                    ->get();

                if ($existingPhones->isNotEmpty()) {
                    $data['phone_duplicate_status'] = 'D';

                    foreach ($existingPhones as $existing) {
                        $existing->phone_duplicate_status = 'D';
                        $existing->save();
                    }

                    $notes[] = 'Shared phone number';
                }
            }

            // CONTACT NAME DUPLICATE CHECK
            $contactName = trim($data['contact'] ?? '');

            if ($contactName !== '') {
                $matchingNames = Contact::whereRaw(
                    'LOWER(TRIM(contact)) = ?',
                    [Str::lower($contactName)]
                )->lockForUpdate()->get();

                if ($matchingNames->isNotEmpty()) {
                    $data['possible_duplicate'] = true;
                    $notes[] = 'Matching contact name';

                    foreach ($matchingNames as $existing) {
                        $existing->possible_duplicate = true;
                        $existing->duplicate_notes = $this->addNote(
                            $existing->duplicate_notes,
                            'Matching contact name'
                        );
                        $existing->save();
                    }
                }
            }

            // BROKERAGE + POSTAL CODE CHECK
            $brokerage = trim($data['brokerage_name'] ?? '');
            $postalCode = trim($data['zip_code'] ?? '');

            if ($brokerage !== '' && $postalCode !== '') {
                $matchingBranches = Contact::whereRaw(
                    'LOWER(TRIM(brokerage_name)) = ?',
                    [Str::lower($brokerage)]
                )
                ->whereRaw(
                    'LOWER(TRIM(zip_code)) = ?',
                    [Str::lower($postalCode)]
                )
                ->lockForUpdate()
                ->get();

                if ($matchingBranches->isNotEmpty()) {
                    $data['possible_duplicate'] = true;
                    $notes[] = 'Matching brokerage and postal code';

                    foreach ($matchingBranches as $existing) {
                        $existing->possible_duplicate = true;
                        $existing->duplicate_notes = $this->addNote(
                            $existing->duplicate_notes,
                            'Matching brokerage and postal code'
                        );
                        $existing->save();
                    }
                }
            }

            // Save notes on the new contact
            $data['duplicate_notes'] = $notes
                ? implode('; ', array_unique($notes))
                : null;

            return Contact::create($data);
        });
    }

    private function addNote(?string $existingNotes, string $newNote): string
    {
        $notes = array_filter(array_map(
            'trim',
            explode(';', $existingNotes ?? '')
        ));

        $notes[] = $newNote;

        return implode('; ', array_unique($notes));
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        return $digits !== '' ? $digits : null;
    }
}
