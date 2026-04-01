<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status'      => 'ok',
            'version'     => config('app.version', '1.0.0'),
            'api_version' => 'v1',
        ]);
    }
}
