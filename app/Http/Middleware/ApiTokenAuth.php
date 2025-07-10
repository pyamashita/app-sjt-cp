<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = 'read'): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'APIトークンが必要です。',
            ], 401);
        }

        $hashedToken = hash('sha256', $token);
        $apiToken = ApiToken::where('token', $hashedToken)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$apiToken) {
            return response()->json([
                'status' => 'error',
                'message' => '無効なAPIトークンです。',
            ], 401);
        }

        // IPアドレス制限チェック
        $clientIp = $request->ip();
        if (!$apiToken->isIpAllowed($clientIp)) {
            return response()->json([
                'status' => 'error',
                'message' => 'このIPアドレスからのアクセスは許可されていません。',
            ], 403);
        }

        // 権限チェック
        if (!$apiToken->hasPermission($permission)) {
            return response()->json([
                'status' => 'error',
                'message' => '必要な権限がありません。',
            ], 403);
        }

        // トークンの使用記録を更新
        $apiToken->update(['last_used_at' => now()]);

        // リクエストにトークン情報を追加
        $request->merge(['api_token' => $apiToken]);

        return $next($request);
    }
}
