<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Carbon;


class activeSubscriptionAndRoleChecks
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user()->load('tenant');
        if (!$user->tenant->expired_at || $user->tenant->expired_at < now()) {
            return response()->json(['message' => 'masa berlangganan anda habis, silahkan selesaikan pembayaran terlebih dahulu']);
        }

        if (in_array($user->role,$roles)) {
            return $next($request);
        } else {
            return response()->json(['message' => 'anda tidak memiliki akses untuk aksi ini']);
        }
    }
}
