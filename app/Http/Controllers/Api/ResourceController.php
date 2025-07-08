<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResourceController extends Controller
{
    /**
     * Get a listing of public resources.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Resource::query()->where('is_public', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $resources = $query->select([
            'id', 'name', 'original_name', 'mime_type', 'size', 
            'description', 'category', 'created_at'
        ])->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $resources,
        ]);
    }

    /**
     * Get a specific resource information.
     */
    public function show(Request $request, Resource $resource): JsonResponse
    {
        if (!$this->canAccess($resource, $request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'アクセス権限がありません。',
            ], 403);
        }

        $resource->load(['accessControls']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $resource->id,
                'name' => $resource->name,
                'original_name' => $resource->original_name,
                'mime_type' => $resource->mime_type,
                'size' => $resource->size,
                'description' => $resource->description,
                'category' => $resource->category,
                'is_public' => $resource->is_public,
                'download_url' => route('api.resources.download', $resource),
                'created_at' => $resource->created_at,
            ],
        ]);
    }

    /**
     * Download the resource file.
     */
    public function download(Request $request, Resource $resource): StreamedResponse|JsonResponse
    {
        if (!$this->canAccess($resource, $request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'アクセス権限がありません。',
            ], 403);
        }

        if (!$resource->file_path || !Storage::disk('public')->exists($resource->file_path)) {
            return response()->json([
                'status' => 'error',
                'message' => 'ファイルが見つかりません。',
            ], 404);
        }

        // アクセスログを記録
        $resource->accessLogs()->create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'access_type' => 'api_download',
            'accessed_at' => now(),
        ]);

        return Storage::disk('public')->download($resource->file_path, $resource->original_name);
    }

    /**
     * Stream the resource file.
     */
    public function stream(Request $request, Resource $resource): StreamedResponse|JsonResponse
    {
        if (!$this->canAccess($resource, $request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'アクセス権限がありません。',
            ], 403);
        }

        if (!$resource->file_path || !Storage::disk('public')->exists($resource->file_path)) {
            return response()->json([
                'status' => 'error',
                'message' => 'ファイルが見つかりません。',
            ], 404);
        }

        // アクセスログを記録
        $resource->accessLogs()->create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'access_type' => 'api_stream',
            'accessed_at' => now(),
        ]);

        return Storage::disk('public')->response($resource->file_path);
    }

    /**
     * Get resource categories.
     */
    public function categories(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Resource::getCategories(),
        ]);
    }

    /**
     * Check if the request can access the resource.
     */
    private function canAccess(Resource $resource, Request $request): bool
    {
        // パブリックリソースは誰でもアクセス可能
        if ($resource->is_public) {
            return true;
        }

        $clientIp = $request->ip();

        // IPアドレス制限チェック
        if (!$resource->isIpAllowed($clientIp)) {
            return false;
        }

        // トークンが必要な場合の認証チェック
        if ($resource->requiresToken()) {
            $token = $request->bearerToken();
            
            if (!$token) {
                return false;
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
                return false;
            }

            // トークンのIPアドレス制限チェック
            if (!$apiToken->isIpAllowed($clientIp)) {
                return false;
            }

            // トークンの権限チェック
            if (!$apiToken->hasPermission('read')) {
                return false;
            }

            // トークンの使用記録を更新
            $apiToken->update(['last_used_at' => now()]);
        }

        return true;
    }

    /**
     * Get API usage statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'トークンが必要です。',
            ], 401);
        }

        $hashedToken = hash('sha256', $token);
        $apiToken = ApiToken::where('token', $hashedToken)
            ->where('is_active', true)
            ->first();

        if (!$apiToken || !$apiToken->hasPermission('stats')) {
            return response()->json([
                'status' => 'error',
                'message' => 'アクセス権限がありません。',
            ], 403);
        }

        $stats = [
            'total_resources' => Resource::count(),
            'public_resources' => Resource::where('is_public', true)->count(),
            'private_resources' => Resource::where('is_public', false)->count(),
            'total_downloads' => Resource::withCount('accessLogs')->get()->sum('access_logs_count'),
            'recent_downloads' => Resource::whereHas('accessLogs', function ($query) {
                $query->where('created_at', '>=', now()->subDays(7));
            })->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ]);
    }
}
