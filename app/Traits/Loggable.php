<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait Loggable
{
    /**
     * Log an informational message
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::info($message, array_merge([
            'model' => class_basename($this),
            'user_id' => auth()?->id(),
        ], $context));
    }

    /**
     * Log a warning message
     */
    protected function logWarning(string $message, array $context = []): void
    {
        Log::warning($message, array_merge([
            'model' => class_basename($this),
            'user_id' => auth()?->id(),
        ], $context));
    }

    /**
     * Log an error message
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error($message, array_merge([
            'model' => class_basename($this),
            'user_id' => auth()?->id(),
        ], $context));
    }

    /**
     * Log a debug message
     */
    protected function logDebug(string $message, array $context = []): void
    {
        Log::debug($message, array_merge([
            'model' => class_basename($this),
            'user_id' => auth()?->id(),
        ], $context));
    }
}
