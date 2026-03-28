<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateMailSettingsRequest;
use App\Http\Requests\Api\V1\UpdateSettingsRequest;
use App\Http\Requests\Api\V1\UpdateSsoSettingsRequest;
use App\Models\AppSetting;
use App\Services\SsoConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'notifications_enabled' => (bool) AppSetting::get('notifications_enabled', '1'),
        ]);
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        foreach ($request->validated() as $key => $value) {
            AppSetting::set($key, $value);
        }

        return response()->json(['message' => 'Settings updated.']);
    }

    public function mail(): JsonResponse
    {
        return response()->json([
            'mail_driver'       => AppSetting::get('mail_driver', config('mail.default')),
            'mail_host'         => AppSetting::get('mail_host', config('mail.mailers.smtp.host')),
            'mail_port'         => AppSetting::get('mail_port', config('mail.mailers.smtp.port')),
            'mail_username'     => AppSetting::get('mail_username', config('mail.mailers.smtp.username')),
            'mail_has_password' => (bool) AppSetting::get('mail_password'),
            'mail_encryption'   => AppSetting::get('mail_encryption', config('mail.mailers.smtp.encryption')),
            'mail_from_address' => AppSetting::get('mail_from_address', config('mail.from.address')),
            'mail_from_name'    => AppSetting::get('mail_from_name', config('mail.from.name')),
        ]);
    }

    public function updateMail(UpdateMailSettingsRequest $request): JsonResponse
    {
        $data = $request->validated();

        foreach ($data as $key => $value) {
            if ($key === 'mail_password' && $value !== null) {
                AppSetting::set($key, Crypt::encryptString($value));
            } else {
                AppSetting::set($key, $value);
            }
        }

        return response()->json(['message' => 'Mail settings updated.']);
    }

    public function testMail(): JsonResponse
    {
        try {
            $user = request()->user();
            Mail::raw('This is a test email from Cypress Dashboard.', function ($message) use ($user) {
                $message->to($user->email)->subject('Test Email — Cypress Dashboard');
            });

            return response()->json(['message' => 'Test email sent to '.$user->email]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send: '.$e->getMessage()], 500);
        }
    }

    public function sso(SsoConfigService $ssoConfig): JsonResponse
    {
        $providers = [];

        foreach (SsoConfigService::PROVIDERS as $key => $meta) {
            $providers[$key] = [
                'label'       => $meta['label'],
                'enabled'     => $ssoConfig->isProviderEnabled($key),
                'configured'  => $ssoConfig->isProviderConfigured($key),
                'client_id'   => $ssoConfig->getClientId($key) ? '••••••••' : null,
                'has_secret'  => (bool) $ssoConfig->getClientSecret($key),
                'redirect_uri' => $ssoConfig->getRedirectUri($key),
            ];
        }

        return response()->json(['providers' => $providers]);
    }

    public function updateSso(UpdateSsoSettingsRequest $request, SsoConfigService $ssoConfig): JsonResponse
    {
        $data     = $request->validated();
        $provider = $data['provider'];

        AppSetting::set("sso_{$provider}_enabled", $data['enabled'] ? '1' : '0');

        if (! empty($data['client_id'])) {
            $ssoConfig->setCredential($provider, 'client_id', $data['client_id']);
        }

        if (! empty($data['client_secret'])) {
            $ssoConfig->setCredential($provider, 'client_secret', $data['client_secret']);
        }

        $ssoConfig->applyRuntimeConfig();

        return response()->json(['message' => "SSO settings for {$provider} updated."]);
    }
}
