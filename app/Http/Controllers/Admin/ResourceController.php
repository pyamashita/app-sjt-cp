<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceRequest;
use App\Models\Resource;
use App\Models\ResourceAccessControl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Resource::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        $resources = $query->with(['accessControls'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $breadcrumbs = [
            ['label' => 'ホーム', 'url' => route('admin.home')],
            ['label' => 'リソース管理', 'url' => route('admin.resources.index')],
        ];

        return view('admin.resources.index', compact('resources', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $breadcrumbs = [
            ['label' => 'ホーム', 'url' => route('admin.home')],
            ['label' => 'リソース管理', 'url' => route('admin.resources.index')],
            ['label' => '新規登録', 'url' => route('admin.resources.create')],
        ];

        return view('admin.resources.create', compact('breadcrumbs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ResourceRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('resources', $filename, 'public');

            $resource = Resource::create([
                'name' => $validated['name'],
                'original_name' => $originalName,
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'description' => $validated['description'] ?? null,
                'is_public' => $validated['is_public'] ?? false,
                'category' => $validated['category'] ?? null,
                'metadata' => [
                    'uploaded_by' => auth()->id(),
                    'uploaded_at' => now(),
                ],
            ]);

            return redirect()->route('admin.resources.show', $resource)
                ->with('success', 'リソースを登録しました。');
        }

        return back()->with('error', 'ファイルのアップロードに失敗しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Resource $resource)
    {
        $resource->load(['accessControls', 'accessLogs' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        $breadcrumbs = [
            ['label' => 'ホーム', 'url' => route('admin.home')],
            ['label' => 'リソース管理', 'url' => route('admin.resources.index')],
            ['label' => $resource->name, 'url' => route('admin.resources.show', $resource)],
        ];

        return view('admin.resources.show', compact('resource', 'breadcrumbs'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Resource $resource)
    {
        $breadcrumbs = [
            ['label' => 'ホーム', 'url' => route('admin.home')],
            ['label' => 'リソース管理', 'url' => route('admin.resources.index')],
            ['label' => $resource->name, 'url' => route('admin.resources.show', $resource)],
            ['label' => '編集', 'url' => route('admin.resources.edit', $resource)],
        ];

        return view('admin.resources.edit', compact('resource', 'breadcrumbs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ResourceRequest $request, Resource $resource)
    {
        $validated = $request->validated();

        if ($request->hasFile('file')) {
            // 古いファイルを削除
            if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
                Storage::disk('public')->delete($resource->file_path);
            }

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('resources', $filename, 'public');

            $validated['original_name'] = $originalName;
            $validated['file_path'] = $path;
            $validated['mime_type'] = $file->getClientMimeType();
            $validated['size'] = $file->getSize();
        }

        $resource->update($validated);

        return redirect()->route('admin.resources.show', $resource)
            ->with('success', 'リソースを更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resource $resource)
    {
        // ファイルを削除
        if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
            Storage::disk('public')->delete($resource->file_path);
        }

        // アクセス制御設定を削除
        $resource->accessControls()->delete();

        // アクセスログを削除
        $resource->accessLogs()->delete();

        // リソースを削除
        $resource->delete();

        return redirect()->route('admin.resources.index')
            ->with('success', 'リソースを削除しました。');
    }

    /**
     * Download the resource file.
     */
    public function download(Resource $resource)
    {
        if (!$resource->file_path || !Storage::disk('public')->exists($resource->file_path)) {
            abort(404, 'ファイルが見つかりません。');
        }

        // アクセスログを記録
        $resource->accessLogs()->create([
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'access_type' => 'download',
            'accessed_at' => now(),
        ]);

        return Storage::disk('public')->download($resource->file_path, $resource->original_name);
    }

    /**
     * Add access control to resource.
     */
    public function addAccessControl(Request $request, Resource $resource)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:ip_whitelist,token_required',
            'value' => 'required|string',
        ]);

        $resource->accessControls()->create([
            'type' => $validated['type'],
            'value' => $validated['value'],
            'is_active' => true,
        ]);

        return back()->with('success', 'アクセス制御を追加しました。');
    }

    /**
     * Remove access control from resource.
     */
    public function removeAccessControl(Resource $resource, ResourceAccessControl $accessControl)
    {
        if ($accessControl->resource_id !== $resource->id) {
            abort(404);
        }

        $accessControl->delete();

        return back()->with('success', 'アクセス制御を削除しました。');
    }

    /**
     * Export resources to CSV.
     */
    public function export(Request $request): Response
    {
        $query = Resource::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        $resources = $query->orderBy('created_at', 'desc')->get();

        $csvData = [];
        
        $csvData[] = Resource::getCsvHeaders();

        foreach ($resources as $resource) {
            $csvData[] = $resource->toCsvArray();
        }

        $csv = '';
        foreach ($csvData as $row) {
            $csv .= implode(',', array_map(function($field) {
                if (strpos($field, ',') !== false || strpos($field, "\n") !== false || strpos($field, '"') !== false) {
                    $field = '"' . str_replace('"', '""', $field) . '"';
                }
                return $field;
            }, $row)) . "\n";
        }

        $csv = "\xEF\xBB\xBF" . $csv;

        $filename = 'リソース一覧_' . date('Y-m-d') . '.csv';
        $encodedFilename = rawurlencode($filename);
        
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encodedFilename);
    }
}
