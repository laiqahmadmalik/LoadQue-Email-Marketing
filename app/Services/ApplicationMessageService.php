<?php

namespace App\Services;

use App\Models\ApplicationMessage;
use Illuminate\Support\Facades\Cache;

class ApplicationMessageService
{
    /**
     * Retrieve one message using its numeric code.
     */
    public function getMessage(
        int $messageCode,
        ?string $language = null,
        array $parameters = []
    ): ?array {
        $messages = $this->getMessages(
            [$messageCode],
            $language,
            [$messageCode => $parameters]
        );

        return $messages[0] ?? null;
    }

    /**
     * Retrieve multiple messages using numeric codes.
     *
     * Error codes are used internally and are never
     * included in the returned display messages.
     */
    public function getMessages(
        array $messageCodes,
        ?string $language = null,
        array $parameters = []
    ): array {
        $language = $language ?? app()->getLocale();

        $results = [];

        foreach (array_unique($messageCodes) as $messageCode) {
            $messageCode = (int) $messageCode;

            $record = $this->findMessage(
                $messageCode,
                $language
            );

            // Fall back to English if translation is unavailable.
            if ($record === null && $language !== 'en') {
                $record = $this->findMessage(
                    $messageCode,
                    'en'
                );
            }

            if ($record === null) {
                continue;
            }

            $message = $record['message'];

            // Replace dynamic placeholders.
            $messageParameters = $parameters[$messageCode] ?? [];

            foreach ($messageParameters as $key => $value) {
                $message = str_replace(
                    ':' . $key,
                    (string) $value,
                    $message
                );
            }

            $results[] = [
                'type' => $record['message_type'],
                'message' => $message,
            ];
        }

        return $results;
    }

    /**
     * Retrieve one active message from the database.
     * Results are cached for 12 hours.
     */
    private function findMessage(
        int $messageCode,
        string $language
    ): ?array {
        $cacheKey = "application_message_{$messageCode}_{$language}";

        return Cache::remember(
            $cacheKey,
            now()->addHours(12),
            function () use ($messageCode, $language) {
                $record = ApplicationMessage::query()
                    ->where('message_code', $messageCode)
                    ->where('language', $language)
                    ->where('is_active', true)
                    ->first(['message_type', 'message']);

                if ($record === null) {
                    return null;
                }

                return [
                    'message_type' => $record->message_type,
                    'message' => $record->message,
                ];
            }
        );
    }

    /**
     * Clear cached messages after they are changed.
     */
    public function clearMessageCache(
        int $messageCode,
        ?string $language = null
    ): void {
        if ($language !== null) {
            Cache::forget(
                "application_message_{$messageCode}_{$language}"
            );

            return;
        }

        foreach (config('app.supported_locales', ['en', 'fr']) as $locale) {
            Cache::forget(
                "application_message_{$messageCode}_{$locale}"
            );
        }
    }


    /**
     * Convert validation errors into customer-facing messages.
     *
     * Numeric error codes are used internally and are not
     * included in the returned display messages.
     */
    public function getValidationMessages(
        array $validationErrors,
        ?string $language = null
    ): array {
        $results = [];

        foreach ($validationErrors as $error) {
            if (
                !isset($error['field'], $error['code'])
                || !is_numeric($error['code'])
            ) {
                continue;
            }

            $message = $this->getMessage(
                (int) $error['code'],
                $language,
                $error['parameters'] ?? []
            );

            if ($message === null) {
                continue;
            }

            $results[] = [
                'field' => $error['field'],
                'type' => $message['type'],
                'message' => $message['message'],
            ];
        }

        return $results;
    }




}
