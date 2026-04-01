<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;

class UpdateMailSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'mail_driver'       => ['nullable', 'in:smtp,sendmail,ses,mailgun,log'],
            'mail_host'         => ['nullable', 'string'],
            'mail_port'         => ['nullable', 'integer'],
            'mail_username'     => ['nullable', 'string'],
            'mail_password'     => ['nullable', 'string'],
            'mail_encryption'   => ['nullable', 'in:tls,ssl,null'],
            'mail_from_address' => ['nullable', 'email'],
            'mail_from_name'    => ['nullable', 'string'],
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
