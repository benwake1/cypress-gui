<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Events;

use App\Models\TestSuite;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuiteHealthBreached
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly TestSuite $suite,
        public readonly float     $currentPassRate,
        public readonly float     $threshold
    ) {}
}
