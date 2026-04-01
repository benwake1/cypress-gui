<?php

namespace App\Http\Requests\Api\V1;

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
