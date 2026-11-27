<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Rules\ValidationRules;
use App\Services\ApplicationResponseService;
use App\Services\ContactDuplicateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    /**
     * Display contacts with pagination, search and sorting.
     */
    public function index(Request $request): JsonResponse
    {
        $sortableFields = [
            'id',
            'brokerage_name',
            'contact',
            'cell_phone',
            'email',
            'authorizing_authority',
            'address',
            'city',
            'province_state',
            'zip_code',
            'duplicate_status',
            'broker_status',
            'email_status',
            'subscription_status',
            'unsubscribed_at',
            'email_invalid_at',
            'last_delivered_at',
            'status_notes',
            'phone_duplicate_status',
            'possible_duplicate',
            'duplicate_notes',
            'created_at',
            'updated_at',
        ];

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort_by' => [
                'nullable',
                'string',
                Rule::in($sortableFields),
            ],
            'sort_direction' => [
                'nullable',
                'string',
                Rule::in(['asc', 'desc']),
            ],
        ]);

        $query = Contact::query();

        if (!empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($q) use ($search) {
                $q->where('brokerage_name', 'LIKE', "%{$search}%")
                    ->orWhere('contact', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('cell_phone', 'LIKE', "%{$search}%");
            });
        }

        $sortBy = $validated['sort_by'] ?? 'id';
        $sortDirection = $validated['sort_direction'] ?? 'desc';

        $query->orderBy($sortBy, $sortDirection);

        if ($sortBy !== 'id') {
            $query->orderBy('id', 'desc');
        }

        $contacts = $query->paginate(
            $validated['per_page'] ?? 25
        );

        return response()->json($contacts);
    }

    /**
     * Display a single contact for editing.
     */
    public function show(int $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);

        return response()->json([
            'data' => $contact,
        ]);
    }

    /**
     * Store a new contact.
     */
    public function store(
        Request $request,
        ContactDuplicateService $duplicateService,
        ApplicationResponseService $responseService
    ): JsonResponse {

        $validation = ValidationRules::validate(
            $request->all(),
            $this->contactValidationRules()
        );

        if (!$validation['valid']) {
            return $responseService->validationError(
                $validation['errors']
            );
        }

        $validated = $request->only($this->contactFields());

        $contact = $duplicateService->createContact($validated);

        return $responseService->success(
            1001,
            $contact,
            201
        );
    }

    /**
     * Update an existing contact.
     */
    public function update(
        Request $request,
        int $id,
        ContactDuplicateService $duplicateService,
        ApplicationResponseService $responseService
    ): JsonResponse {

        $contact = Contact::findOrFail($id);

        $validation = ValidationRules::validate(
            $request->all(),
            $this->contactValidationRules()
        );

        if (!$validation['valid']) {
            return $responseService->validationError(
                $validation['errors']
            );
        }

        $validated = $request->only($this->contactFields());

        $contact = $duplicateService->updateContact(
            $contact,
            $validated
        );

        return $responseService->success(
            1002,
            $contact
        );
    }

    /**
     * Soft delete a contact.
     */
    public function destroy(
        int $id,
        ApplicationResponseService $responseService
    ): JsonResponse {

        $contact = Contact::findOrFail($id);

        $contact->delete();

        return $responseService->success(
            1003,
            [
                'id' => $contact->id,
                'contact_id' => $contact->contact_id,
                'deleted_at' => $contact->deleted_at,
                'deleted_by' => $contact->deleted_by,
            ]
        );
    }

    /**
     * Display soft-deleted contacts (admin only).
     */
    public function deleted(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Contact::onlyTrashed()
            ->leftJoin('users', 'contacts.deleted_by', '=', 'users.id')
            ->select('contacts.*', 'users.name as deleted_by_name');

        if (!empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($q) use ($search) {
                $q->where('contacts.brokerage_name', 'LIKE', "%{$search}%")
                    ->orWhere('contacts.contact', 'LIKE', "%{$search}%")
                    ->orWhere('contacts.email', 'LIKE', "%{$search}%")
                    ->orWhere('contacts.cell_phone', 'LIKE', "%{$search}%")
                    ->orWhere('contacts.contact_id', 'LIKE', "%{$search}%");
            });
        }

        $contacts = $query
            ->orderByDesc('contacts.deleted_at')
            ->paginate($validated['per_page'] ?? 25);

        return response()->json($contacts);
    }

    /**
     * Restore a soft-deleted contact (admin only).
     */
    public function restore(
        Request $request,
        int $id,
        ApplicationResponseService $responseService
    ): JsonResponse {

        abort_unless($request->user()->isAdmin(), 403);

        $contact = Contact::onlyTrashed()->findOrFail($id);

        $contact->restore();

        return $responseService->success(
            1005,
            [
                'id' => $contact->id,
                'contact_id' => $contact->contact_id,
                'brokerage_name' => $contact->brokerage_name,
            ]
        );
    }

    /**
     * Permanently delete a soft-deleted contact (admin only).
     */
    public function forceDestroy(
        Request $request,
        int $id,
        ApplicationResponseService $responseService
    ): JsonResponse {

        abort_unless($request->user()->isAdmin(), 403);

        $contact = Contact::onlyTrashed()->findOrFail($id);

        $contactDetails = [
            'id' => $contact->id,
            'contact_id' => $contact->contact_id,
            'brokerage_name' => $contact->brokerage_name,
        ];

        $contact->forceDelete();

        return $responseService->success(
            1006,
            $contactDetails
        );
    }

    /**
     * Shared contact validation rules.
     */
    private function contactValidationRules(): array
    {
        return [
            'brokerage_name' => [
                'required',
                'string',
                'max:255',
            ],

            'contact' => [
                'nullable',
                'string',
                'max:255',
            ],

            'cell_phone' => ValidationRules::phone(false),

            'email' => ValidationRules::email(false),

            'authorizing_authority' => [
                'nullable',
                'string',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'city' => [
                'nullable',
                'string',
                'max:255',
            ],

            'province_state' => [
                'nullable',
                'string',
                'max:255',
            ],

            'zip_code' => [
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }

    /**
     * Fields allowed when creating or updating contacts.
     */
    private function contactFields(): array
    {
        return [
            'brokerage_name',
            'contact',
            'authorizing_authority',
            'email',
            'cell_phone',
            'address',
            'city',
            'province_state',
            'zip_code',
        ];
    }
}
