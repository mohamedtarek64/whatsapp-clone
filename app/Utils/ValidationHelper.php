<?php

namespace App\Utils;

class ValidationHelper
{
    /**
     * Validate email format
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate password strength
     */
    public static function isStrongPassword(string $password): bool
    {
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password) &&
               preg_match('/[!@#$%^&*()_+=\-\[\]{};:\'",.<>?\\/\\\\|`~]/', $password);
    }

    /**
     * Validate username format
     */
    public static function isValidUsername(string $username): bool
    {
        return preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username);
    }

    /**
     * Sanitize input string
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate file upload
     */
    public static function isValidFileUpload($file, array $allowedExtensions = [], int $maxSize = 5242880): bool
    {
        if (!$file->isValid()) {
            return false;
        }

        // Check file size (default 5MB)
        if ($file->getSize() > $maxSize) {
            return false;
        }

        // Check extension if specified
        if (!empty($allowedExtensions)) {
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowedExtensions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get validation error messages
     */
    public static function getErrorMessages(array $errors): array
    {
        $messages = [];

        foreach ($errors as $field => $fieldErrors) {
            $messages[$field] = is_array($fieldErrors) ? $fieldErrors[0] : $fieldErrors;
        }

        return $messages;
    }
}
