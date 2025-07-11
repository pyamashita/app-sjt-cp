<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $servers = Server::orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.servers.index', compact('servers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.servers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:player,database,dns,other',
            'ip_address' => 'required|ip',
            'hostname' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'web_document_root' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Server::create($validated);

        return redirect()
            ->route('admin.servers.index')
            ->with('success', 'サーバーが正常に追加されました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Server $server): View
    {
        $server->load(['databases', 'databaseUsers', 'dnsRecords']);
        
        return view('admin.servers.show', compact('server'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server): View
    {
        return view('admin.servers.edit', compact('server'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Server $server): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:player,database,dns,other',
            'ip_address' => 'required|ip',
            'hostname' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'web_document_root' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $server->update($validated);

        return redirect()
            ->route('admin.servers.index')
            ->with('success', 'サーバー情報が正常に更新されました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Server $server): RedirectResponse
    {
        $server->delete();

        return redirect()
            ->route('admin.servers.index')
            ->with('success', 'サーバーが正常に削除されました。');
    }
}
