<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class ApplicationResponseService
{
    public function __construct(
        protected ApplicationMessageService $messageService
    ) {
    }

    /**
     * Return validation errors using customer-facing messages.
     */
    public function validationError(
        array $validationErrors,
        ?string $language = null
    ): JsonResponse {

        $messages = $this->messageService->getValidationMessages(
            $validationErrors,
            $language
        );

        return response()->json([
            'success' => false,
            'messages' => $messages,
            'data' => null,
        ], 422);
    }

    /**
     * Return a successful application response.
     */
    public function success(
        int $messageCode,
        mixed $data = null,
        int $status = 200,
        ?string $language = null
    ): JsonResponse {

        $message = $this->messageService->getMessage(
            $messageCode,
            $language
        );

        return response()->json([
            'success' => true,
            'messages' => $message ? [
                [
                    'type' => $message['type'],
                    'message' => $message['message'],
                ],
            ] : [],
            'data' => $data,
        ], $status);
    }

    /**
     * Return a customer-facing error response.
     */
    public function error(
        int $messageCode,
        int $status = 400,
        ?string $language = null,
        mixed $data = null
    ): JsonResponse {

        $message = $this->messageService->getMessage(
            $messageCode,
            $language
        );

        return response()->json([
            'success' => false,
            'messages' => $message ? [
                [
                    'type' => $message['type'],
                    'message' => $message['message'],
                ],
            ] : [],
            'data' => $data,
        ], $status);
    }
}
