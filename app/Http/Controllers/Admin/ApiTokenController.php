<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiTokenRequest;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ApiTokenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ApiToken::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('expires_at')) {
            if ($request->expires_at === 'expired') {
                $query->where('expires_at', '<', now());
            } elseif ($request->expires_at === 'active') {
                $query->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                });
            }
        }

        $tokens = $query->orderBy('created_at', 'desc')->paginate(20);

        $breadcrumbs = [
            ['label' => 'ホーム', 'url' => route('admin.home')],
            ['label' => 'APIトークン管理', 'url' => route('admin.api-tokens.index')],
        ];

        return view('admin.api-tokens.index', compact('tokens', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $breadcrumbs = [
            ['label' => 'ホーム', 'url' => route('admin.home')],
            ['label' => 'APIトークン管理', 'url' => route('admin.api-tokens.index')],
            ['label' => '新規作成', 'url' => route('admin.api-tokens.create')],
        ];

        return view('admin.api-tokens.create', compact('breadcrumbs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ApiTokenRequest $request)
    {
        $validated = $request->validated();

        $token = ApiToken::create([
            'name' => $validated['name'],
            'token' => hash('sha256', $plainTextToken = Str::random(64)),
            'permissions' => $validated['permissions'],
            'allowed_ips' => $validated['allowed_ips'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'expires_at' => $validated['expires_at'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.api-tokens.show', $token)
            ->with('success', 'APIトークンを作成しました。')
            ->with('token', $plainTextToken);
    }

    /**
     * Display the specified resource.
     */
    public function show(ApiToken $apiToken)
    {
        $breadcrumbs = [
            ['label' => 'ホーム', 'url' => route('admin.home')],
            ['label' => 'APIトークン管理', 'url' => route('admin.api-tokens.index')],
            ['label' => $apiToken->name, 'url' => route('admin.api-tokens.show', $apiToken)],
        ];

        return view('admin.api-tokens.show', compact('apiToken', 'breadcrumbs'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ApiToken $apiToken)
    {
        $breadcrumbs = [
            ['label' => 'ホーム', 'url' => route('admin.home')],
            ['label' => 'APIトークン管理', 'url' => route('admin.api-tokens.index')],
            ['label' => $apiToken->name, 'url' => route('admin.api-tokens.show', $apiToken)],
            ['label' => '編集', 'url' => route('admin.api-tokens.edit', $apiToken)],
        ];

        return view('admin.api-tokens.edit', compact('apiToken', 'breadcrumbs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ApiTokenRequest $request, ApiToken $apiToken)
    {
        $validated = $request->validated();

        $apiToken->update($validated);

        return redirect()->route('admin.api-tokens.show', $apiToken)
            ->with('success', 'APIトークンを更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApiToken $apiToken)
    {
        $apiToken->delete();

        return redirect()->route('admin.api-tokens.index')
            ->with('success', 'APIトークンを削除しました。');
    }

    /**
     * Regenerate the API token.
     */
    public function regenerate(ApiToken $apiToken)
    {
        $plainTextToken = Str::random(64);
        $apiToken->update([
            'token' => hash('sha256', $plainTextToken),
        ]);

        return redirect()->route('admin.api-tokens.show', $apiToken)
            ->with('success', 'APIトークンを再生成しました。')
            ->with('token', $plainTextToken);
    }

    /**
     * Toggle the active status of the API token.
     */
    public function toggle(ApiToken $apiToken)
    {
        $apiToken->update([
            'is_active' => !$apiToken->is_active,
        ]);

        $status = $apiToken->is_active ? '有効' : '無効';
        
        return back()->with('success', "APIトークンを{$status}にしました。");
    }

    /**
     * Export API tokens to CSV.
     */
    public function export(Request $request): Response
    {
        $query = ApiToken::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $tokens = $query->orderBy('created_at', 'desc')->get();

        $csvData = [];
        
        $csvData[] = ApiToken::getCsvHeaders();

        foreach ($tokens as $token) {
            $csvData[] = $token->toCsvArray();
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

        $filename = 'APIトークン一覧_' . date('Y-m-d') . '.csv';
        $encodedFilename = rawurlencode($filename);
        
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encodedFilename);
    }
}
