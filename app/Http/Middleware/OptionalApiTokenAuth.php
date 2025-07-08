<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptionalApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token) {
            $hashedToken = hash('sha256', $token);
            $apiToken = ApiToken::where('token', $hashedToken)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->first();

            if ($apiToken) {
                // IPアドレス制限チェック
                $clientIp = $request->ip();
                if ($apiToken->isIpAllowed($clientIp)) {
                    // トークンの使用記録を更新
                    $apiToken->update(['last_used_at' => now()]);
                    
                    // リクエストにトークン情報を追加
                    $request->merge(['api_token' => $apiToken]);
                }
            }
        }

        return $next($request);
    }
}
