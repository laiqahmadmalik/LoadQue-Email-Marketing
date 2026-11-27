<?php

namespace Database\Seeders;

use App\Models\ApplicationMessage;
use Illuminate\Database\Seeder;

class ApplicationMessageSeeder extends Seeder
{
    /**
     * Register LOADQUE's standard application messages.
     */
    public function run(): void
    {
        $messages = [
            // GENERAL VALIDATION ERRORS: 101–199
            [101, 'error', 'This field is required.'],
            [102, 'error', 'Please enter a valid name.'],
            [103, 'error', 'This field must contain at least :min characters.'],
            [104, 'error', 'This field cannot exceed :max characters.'],
            [105, 'error', 'The submitted value has an invalid format.'],

            // CONTACT VALIDATION ERRORS: 201–299
            [201, 'error', 'Please enter a valid email address.'],
            [202, 'error', 'Please enter a valid phone number.'],
            [203, 'error', 'Please enter a valid postal or ZIP code.'],
            [204, 'error', 'Please enter a valid website address.'],
            [205, 'error', 'Please enter a valid date.'],

            // DATABASE AND RECORD ERRORS: 401–499
            [401, 'error', 'This email address is already registered.'],
            [402, 'error', 'This phone number is already registered.'],
            [403, 'error', 'The requested record could not be found.'],
            [404, 'error', 'This record already exists.'],

            // SYSTEM ERRORS: 501–599
            [501, 'error', 'Something went wrong. Please try again.'],
            [502, 'error', 'We could not complete your request.'],
            [503, 'error', 'The service is temporarily unavailable.'],

            // AUTHENTICATION ERRORS: 601–699
            [601, 'error', 'Invalid username or password.'],
            [602, 'error', 'Your session has expired. Please sign in again.'],

            // PERMISSION ERRORS: 701–799
            [701, 'error', 'You are not authorized to perform this action.'],
            [702, 'error', 'You do not have permission to access this resource.'],

            // SUCCESS MESSAGES: 1001–1099
            [1001, 'success', 'Contact created successfully.'],
            [1002, 'success', 'Contact updated successfully.'],
            [1003, 'success', 'Contact deleted successfully.'],
            [1004, 'success', 'Changes saved successfully.'],
            [1005, 'success', 'Record restored successfully.'],
            [1006, 'success', 'Record permanently deleted successfully.'],

            // WARNING MESSAGES: 2001–2099
            [2001, 'warning', 'Some information needs your attention.'],
            [2002, 'warning', 'You have unsaved changes.'],
            [2003, 'warning', 'This action cannot be undone.'],

            // INFORMATION MESSAGES: 3001–3099
            [3001, 'info', 'Your request is being processed.'],
            [3002, 'info', 'No records were found.'],
            [3003, 'info', 'Your information has been received.'],
        ];

        foreach ($messages as [$code, $type, $message]) {
            ApplicationMessage::firstOrCreate(
                [
                    'message_code' => $code,
                    'language' => 'en',
                ],
                [
                    'message_type' => $type,
                    'message' => $message,
                    'is_active' => true,
                ]
            );
        }
    }
}
