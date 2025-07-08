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
     * Get a listing of resources (public only for unauthenticated, all for authenticated).
     */
    public function index(Request $request): JsonResponse
    {
        $apiToken = $request->get('api_token');
        
        $query = Resource::query();
        
        // 認証されていない場合は公開リソースのみ
        if (!$apiToken) {
            $query->where('is_public', true);
        }
        // 認証済みの場合、権限に応じてフィルタ
        elseif (!$apiToken->hasPermission('manage')) {
            // 管理権限がない場合は公開リソースのみ
            $query->where('is_public', true);
        }
        // 管理権限がある場合はすべてのリソースにアクセス可能

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('is_public') && $apiToken && $apiToken->hasPermission('manage')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        $resources = $query->select([
            'id', 'name', 'original_name', 'mime_type', 'size', 
            'description', 'category', 'is_public', 'created_at'
        ])->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $resources,
            'authenticated' => (bool) $apiToken,
            'permissions' => $apiToken ? $apiToken->permissions : [],
        ]);
    }

    /**
     * Get a specific resource information.
     */
    public function show(Request $request, Resource $resource): JsonResponse
    {
        $apiToken = $request->get('api_token');
        
        // 非公開リソースの場合は認証が必要
        if (!$resource->is_public && !$apiToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'このリソースにアクセスするには認証が必要です。',
            ], 401);
        }

        // 非公開リソースで管理権限がない場合は拒否
        if (!$resource->is_public && !$apiToken->hasPermission('manage')) {
            return response()->json([
                'status' => 'error',
                'message' => 'このリソースにアクセスする権限がありません。',
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
                'download_url' => url('/api/resources/' . $resource->id . '/download'),
                'created_at' => $resource->created_at,
            ],
        ]);
    }

    /**
     * Download the resource file.
     */
    public function download(Request $request, Resource $resource): StreamedResponse|JsonResponse
    {
        $apiToken = $request->get('api_token');
        
        // 非公開リソースの場合は認証が必要
        if (!$resource->is_public && !$apiToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'このリソースにアクセスするには認証が必要です。',
            ], 401);
        }

        // 非公開リソースで管理権限がない場合は拒否
        if (!$resource->is_public && !$apiToken->hasPermission('manage')) {
            return response()->json([
                'status' => 'error',
                'message' => 'このリソースにアクセスする権限がありません。',
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
        $apiToken = $request->get('api_token');
        
        // 非公開リソースの場合は認証が必要
        if (!$resource->is_public && !$apiToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'このリソースにアクセスするには認証が必要です。',
            ], 401);
        }

        // 非公開リソースで管理権限がない場合は拒否
        if (!$resource->is_public && !$apiToken->hasPermission('manage')) {
            return response()->json([
                'status' => 'error',
                'message' => 'このリソースにアクセスする権限がありません。',
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
     * Get API usage statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $apiToken = $request->get('api_token');
        
        if (!$apiToken || !$apiToken->hasPermission('stats')) {
            return response()->json([
                'status' => 'error',
                'message' => '統計情報を取得する権限がありません。',
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
