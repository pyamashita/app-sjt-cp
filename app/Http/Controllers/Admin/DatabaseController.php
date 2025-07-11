<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Database;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DatabaseController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $serverId = $request->get('server_id');
        $server = Server::findOrFail($serverId);
        
        if (!$server->isDatabaseServer()) {
            abort(403, 'このサーバはデータベースサーバではありません。');
        }
        
        return view('admin.databases.create', compact('server'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:servers,id',
            'name' => 'required|string|max:255',
            'charset' => 'required|string|max:50',
            'collation' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'server_id.required' => 'サーバIDが必要です。',
            'server_id.exists' => '指定されたサーバが存在しません。',
            'name.required' => 'データベース名を入力してください。',
            'charset.required' => '文字セットを選択してください。',
            'collation.required' => '照合順序を選択してください。',
        ]);

        $server = Server::findOrFail($validated['server_id']);
        
        if (!$server->isDatabaseServer()) {
            return back()->withErrors(['server_id' => 'このサーバはデータベースサーバではありません。']);
        }

        $validated['is_active'] = $request->has('is_active');

        Database::create($validated);

        return redirect()
            ->route('admin.servers.show', $server)
            ->with('success', 'データベースが正常に追加されました。');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Database $database): View
    {
        $server = $database->server;
        return view('admin.databases.edit', compact('database', 'server'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Database $database): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'charset' => 'required|string|max:50',
            'collation' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'データベース名を入力してください。',
            'charset.required' => '文字セットを選択してください。',
            'collation.required' => '照合順序を選択してください。',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $database->update($validated);

        return redirect()
            ->route('admin.servers.show', $database->server)
            ->with('success', 'データベース情報が正常に更新されました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Database $database): RedirectResponse
    {
        $server = $database->server;
        $database->delete();

        return redirect()
            ->route('admin.servers.show', $server)
            ->with('success', 'データベースが正常に削除されました。');
    }
}
