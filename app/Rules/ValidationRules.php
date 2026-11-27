<?php

namespace App\Rules;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ValidationRules
{
    /*
    |--------------------------------------------------------------------------
    | CENTRAL FORM VALIDATION ENGINE
    |--------------------------------------------------------------------------
    */

    public static function validate(
        array $data,
        array $rules,
        array $messages = [],
        array $attributes = []
    ): array {
        $validator = Validator::make(
            $data,
            $rules,
            $messages,
            $attributes
        );

        if ($validator->passes()) {
            return [
                'valid' => true,
                'errors' => [],
            ];
        }

        $errors = [];
        $seenErrors = [];

        foreach ($validator->failed() as $field => $failedRules) {
            foreach ($failedRules as $ruleName => $parameters) {
                $rule = strtolower($ruleName);

                $code = self::getErrorCode(
                    $field,
                    $rule
                );

                $errorKey = $field . ':' . $code;

                // Prevent duplicate messages for the same field.
                if (isset($seenErrors[$errorKey])) {
                    continue;
                }

                $seenErrors[$errorKey] = true;

                $errors[] = [
                    'field' => $field,
                    'code' => $code,
                    'parameters' => self::getMessageParameters(
                        $rule,
                        $parameters
                    ),
                ];
            }
        }

        return [
            'valid' => false,
            'errors' => $errors,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | NUMERIC ERROR CODE MAPPING
    |--------------------------------------------------------------------------
    */

    private static function getErrorCode(
        string $field,
        string $rule
    ): int {
        $field = strtolower($field);

        // Required field
        if ($rule === 'required') {
            return 101;
        }

        // Email validation
        if ($field === 'email' && in_array($rule, [
            'email',
            'regex',
        ], true)) {
            return 201;
        }

        // Phone validation
        if (in_array($field, [
            'phone',
            'cell_phone',
            'business_phone',
            'fax',
        ], true) && in_array($rule, [
            'regex',
            'max',
            'min',
        ], true)) {
            return 202;
        }

        // Other contact validation
        if ($rule === 'url') {
            return 204;
        }

        if ($rule === 'date') {
            return 205;
        }

        // Duplicate records
        if ($rule === 'unique') {
            if ($field === 'email') {
                return 401;
            }

            if (in_array($field, [
                'phone',
                'cell_phone',
                'business_phone',
            ], true)) {
                return 402;
            }

            return 404;
        }

        // Record not found
        if ($rule === 'exists') {
            return 403;
        }

        // Length validation
        if ($rule === 'min') {
            return 103;
        }

        if ($rule === 'max') {
            return 104;
        }

        // General validation fallback
        return 105;
    }


    /*
    |--------------------------------------------------------------------------
    | MESSAGE PARAMETERS
    |--------------------------------------------------------------------------
    */

    private static function getMessageParameters(
        string $rule,
        array $parameters
    ): array {
        if ($rule === 'min' && isset($parameters[0])) {
            return ['min' => $parameters[0]];
        }

        if ($rule === 'max' && isset($parameters[0])) {
            return ['max' => $parameters[0]];
        }

        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | COMMON LARAVEL VALIDATION RULES
    |--------------------------------------------------------------------------
    */

    public static function required(): array
    {
        return ['required'];
    }

    public static function nullable(): array
    {
        return ['nullable'];
    }

    public static function string(): array
    {
        return ['string'];
    }

    public static function integer(): array
    {
        return ['integer'];
    }

    public static function numeric(): array
    {
        return ['numeric'];
    }

    public static function boolean(): array
    {
        return ['boolean'];
    }

    public static function array(): array
    {
        return ['array'];
    }

    public static function url(): array
    {
        return ['url'];
    }

    public static function date(): array
    {
        return ['date'];
    }

    public static function min(int|float $value): array
    {
        return ["min:$value"];
    }

    public static function max(int|float $value): array
    {
        return ["max:$value"];
    }

    public static function in(array $values): array
    {
        return [Rule::in($values)];
    }

    public static function confirmed(): array
    {
        return ['confirmed'];
    }

    public static function unique(
        string $table,
        string $column = 'id',
        int|string|null $ignoreId = null,
        string $idColumn = 'id'
    ): array {
        $rule = Rule::unique($table, $column);

        if ($ignoreId !== null) {
            $rule->ignore($ignoreId, $idColumn);
        }

        return [$rule];
    }

    public static function exists(
        string $table,
        string $column = 'id'
    ): array {
        return [Rule::exists($table, $column)];
    }


    /*
    |--------------------------------------------------------------------------
    | CONTACT VALIDATION RULES
    |--------------------------------------------------------------------------
    */

    public static function email(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'email:rfc',
            'regex:/^[^@\s]+@(?:[A-Za-z0-9-]+\.)+[A-Za-z]{2,}$/',
            'max:255',
        ];
    }

    public static function phone(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:50',
            'regex:/^(?=(?:\D*\d){7,15}\D*$)\+?[0-9\s().-]+$/',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | VALUE CHECKING HELPERS
    |--------------------------------------------------------------------------
    */

    public static function isEmpty(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_array($value)) {
            return count($value) === 0;
        }

        return false;
    }

    public static function isNull(mixed $value): bool
    {
        return $value === null;
    }

    public static function isBoolean(mixed $value): bool
    {
        return is_bool($value)
            || $value === 0
            || $value === 1
            || $value === '0'
            || $value === '1';
    }

    public static function isString(mixed $value): bool
    {
        return is_string($value);
    }

    public static function isInteger(mixed $value): bool
    {
        return is_int($value);
    }

    public static function isNumeric(mixed $value): bool
    {
        return is_numeric($value);
    }

    public static function isArray(mixed $value): bool
    {
        return is_array($value);
    }

    public static function isEmail(mixed $value): bool
    {
        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        return Validator::make(
            ['value' => $value],
            ['value' => self::email(true)]
        )->passes();
    }

    public static function isValidPhone(mixed $value): bool
    {
        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        return Validator::make(
            ['value' => $value],
            ['value' => self::phone(true)]
        )->passes();
    }
}
