<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Rules;

use Cron\CronExpression;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCronExpression implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        try {
            new CronExpression($value);
        } catch (\Throwable) {
            $fail('The cron expression is invalid. Example: 0 9 * * 1-5');
        }
    }
}
