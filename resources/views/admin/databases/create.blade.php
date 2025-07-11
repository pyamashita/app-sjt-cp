@extends('layouts.admin')

@section('title', 'データベース追加 - SJT-CP')

@php
    $pageTitle = 'データベース追加';
    $pageDescription = $server->hostname . ' にデータベースを追加します';
    $breadcrumbs = [
        ['label' => 'サーバ管理', 'url' => route('admin.servers.index')],
        ['label' => $server->hostname, 'url' => route('admin.servers.show', $server)],
        ['label' => 'データベース追加', 'url' => '#']
    ];
@endphp

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">データベース情報入力</h3>
                <p class="text-sm text-gray-600 mt-1">サーバ: {{ $server->hostname }} ({{ $server->type_display_name }})</p>
            </div>
            
            <form method="POST" action="{{ route('admin.databases.store') }}" class="px-6 py-6 space-y-6">
                @csrf
                <input type="hidden" name="server_id" value="{{ $server->id }}">
                
                <!-- データベース名 -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        データベース名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           placeholder="例: competition_db"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- 文字セット -->
                <div>
                    <label for="charset" class="block text-sm font-medium text-gray-700 mb-2">
                        文字セット <span class="text-red-500">*</span>
                    </label>
                    <select id="charset" name="charset" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">選択してください</option>
                        <option value="utf8mb4" {{ old('charset', 'utf8mb4') === 'utf8mb4' ? 'selected' : '' }}>utf8mb4 (推奨)</option>
                        <option value="utf8" {{ old('charset') === 'utf8' ? 'selected' : '' }}>utf8</option>
                        <option value="latin1" {{ old('charset') === 'latin1' ? 'selected' : '' }}>latin1</option>
                    </select>
                    @error('charset')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- 照合順序 -->
                <div>
                    <label for="collation" class="block text-sm font-medium text-gray-700 mb-2">
                        照合順序 <span class="text-red-500">*</span>
                    </label>
                    <select id="collation" name="collation" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">選択してください</option>
                        <option value="utf8mb4_unicode_ci" {{ old('collation', 'utf8mb4_unicode_ci') === 'utf8mb4_unicode_ci' ? 'selected' : '' }}>utf8mb4_unicode_ci (推奨)</option>
                        <option value="utf8mb4_general_ci" {{ old('collation') === 'utf8mb4_general_ci' ? 'selected' : '' }}>utf8mb4_general_ci</option>
                        <option value="utf8_unicode_ci" {{ old('collation') === 'utf8_unicode_ci' ? 'selected' : '' }}>utf8_unicode_ci</option>
                        <option value="utf8_general_ci" {{ old('collation') === 'utf8_general_ci' ? 'selected' : '' }}>utf8_general_ci</option>
                        <option value="latin1_swedish_ci" {{ old('collation') === 'latin1_swedish_ci' ? 'selected' : '' }}>latin1_swedish_ci</option>
                    </select>
                    @error('collation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- 説明 -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        説明
                    </label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="データベースの用途や特記事項を入力してください"
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- アクティブ状態 -->
                <div>
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}
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
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        データベースを追加
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // 文字セット変更時に照合順序を自動更新
        document.getElementById('charset').addEventListener('change', function() {
            const charset = this.value;
            const collationSelect = document.getElementById('collation');
            
            // 照合順序のオプションをクリア
            collationSelect.innerHTML = '<option value="">選択してください</option>';
            
            if (charset === 'utf8mb4') {
                collationSelect.innerHTML += '<option value="utf8mb4_unicode_ci" selected>utf8mb4_unicode_ci (推奨)</option>';
                collationSelect.innerHTML += '<option value="utf8mb4_general_ci">utf8mb4_general_ci</option>';
            } else if (charset === 'utf8') {
                collationSelect.innerHTML += '<option value="utf8_unicode_ci" selected>utf8_unicode_ci (推奨)</option>';
                collationSelect.innerHTML += '<option value="utf8_general_ci">utf8_general_ci</option>';
            } else if (charset === 'latin1') {
                collationSelect.innerHTML += '<option value="latin1_swedish_ci" selected>latin1_swedish_ci</option>';
            }
        });
    </script>
    @endpush
@endsection