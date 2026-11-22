<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Services\ContactDuplicateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display contacts with pagination and search.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
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

        $contacts = $query
            ->orderBy('id', 'desc')
            ->paginate($validated['per_page'] ?? 25);

        return response()->json($contacts);
    }

    /**
     * Store a new contact.
     */
    public function store(
        Request $request,
        ContactDuplicateService $duplicateService
    ): JsonResponse {
        $validated = $request->validate([
            'brokerage_name' => ['required', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'cell_phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'authorizing_authority' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'province_state' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:50'],
        ]);

        $contact = $duplicateService->createContact($validated);

        return response()->json([
            'message' => 'Contact created successfully.',
            'data' => $contact,
        ], 201);
    }
}
