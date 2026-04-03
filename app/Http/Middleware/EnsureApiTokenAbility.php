<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTokenAbility
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        if (! $request->user() || ! $request->user()->currentAccessToken()) {
            abort(401, 'Unauthenticated.');
        }

        if (! $request->user()->tokenCan($ability)) {
            abort(403, 'Token does not have the required ability.');
        }

        return $next($request);
    }
}
