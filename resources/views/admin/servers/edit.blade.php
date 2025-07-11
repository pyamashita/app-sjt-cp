@extends('layouts.admin')

@section('title', 'サーバ編集 - SJT-CP')

@php
    $pageTitle = 'サーバ編集';
    $pageDescription = 'サーバ情報を編集します';
    $breadcrumbs = [
        ['label' => 'サーバ管理', 'url' => route('admin.servers.index')],
        ['label' => $server->hostname, 'url' => route('admin.servers.show', $server)],
        ['label' => '編集', 'url' => '#']
    ];
@endphp

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">サーバ情報編集</h3>
            </div>
            
            <form method="POST" action="{{ route('admin.servers.update', $server) }}" class="px-6 py-6 space-y-6">
                @csrf
                @method('PUT')
                
                <!-- サーバ種類 -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        サーバ種類 <span class="text-red-500">*</span>
                    </label>
                    <select id="type" name="type" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">選択してください</option>
                        @foreach(\App\Models\Server::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ old('type', $server->type) === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- IPアドレス -->
                <div>
                    <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-2">
                        IPアドレス <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="ip_address" name="ip_address" value="{{ old('ip_address', $server->ip_address) }}" required
                           placeholder="例: 192.168.1.100"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    @error('ip_address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- ホスト名 -->
                <div>
                    <label for="hostname" class="block text-sm font-medium text-gray-700 mb-2">
                        ホスト名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="hostname" name="hostname" value="{{ old('hostname', $server->hostname) }}" required
                           placeholder="例: server01.competition.local"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    @error('hostname')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- ユーザ名 -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        ユーザ名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="username" name="username" value="{{ old('username', $server->username) }}" required
                           placeholder="例: admin"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    @error('username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- パスワード -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        パスワード <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password" name="password" value="{{ old('password', $server->password) }}" required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Webドキュメントルート -->
                <div>
                    <label for="web_document_root" class="block text-sm font-medium text-gray-700 mb-2">
                        Webドキュメントルート <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="web_document_root" name="web_document_root" value="{{ old('web_document_root', $server->web_document_root) }}" required
                           placeholder="例: /var/www/html"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    @error('web_document_root')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- 説明 -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        説明
                    </label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="サーバの用途や特記事項を入力してください"
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('description', $server->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- アクティブ状態 -->
                <div>
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', $server->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            アクティブ（稼働中状態にする）
                        </label>
                    </div>
                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- ボタン -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.servers.show', $server) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        戻る
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        更新
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection