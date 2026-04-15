<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Requests\Api\V1;

use App\Rules\ValidCronExpression;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;

class UpdateTestSuiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'name'                 => ['sometimes', 'string', 'max:255'],
            'spec_pattern'         => ['nullable', 'string'],
            'branch_override'      => ['nullable', 'string', 'max:255'],
            'playwright_projects'  => ['nullable', 'array'],
            'playwright_workers'   => ['nullable', 'integer', 'min:1'],
            'playwright_retries'   => ['nullable', 'integer', 'min:0'],
            'timeout_minutes'      => ['nullable', 'integer', 'min:1', 'max:360'],
            'env_variables'        => ['nullable', 'array'],
            'schedule_cron'        => ['nullable', 'string', 'max:100', new ValidCronExpression()],
            'schedule_enabled'     => ['nullable', 'boolean'],
            'schedule_timezone'    => ['nullable', 'string', 'max:64', 'timezone'],
            'pass_rate_threshold'  => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException(
            json_encode(['message' => 'You are not authorized to perform this action.']),
            403
        );
    }
}
