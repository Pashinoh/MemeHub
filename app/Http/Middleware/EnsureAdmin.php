<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $adminEmails = array_map('strtolower', (array) config('services.account.admin_emails', []));

        if (! in_array(strtolower((string) $user->email), $adminEmails, true)) {
            abort(403);
        }

        return $next($request);
    }
}
