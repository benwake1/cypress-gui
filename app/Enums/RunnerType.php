<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Enums;

enum RunnerType: string
{
    case Cypress = 'cypress';
    case Playwright = 'playwright';

    public function label(): string
    {
        return match ($this) {
            self::Cypress => 'Cypress',
            self::Playwright => 'Playwright',
        };
    }
}
