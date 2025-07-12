@extends('layouts.admin')

@section('title', '外部接続設定編集 - SJT-CP')

@php
    $pageTitle = '外部接続設定編集';
    $pageDescription = $connection->name . 'の設定を変更します';
@endphp

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('admin.external-connections.index') }}" 
               class="text-gray-600 hover:text-gray-900">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ $pageDescription }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.external-connections.update', $connection) }}" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-lg shadow p-6 space-y-6">
            <!-- 基本情報 -->
            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">基本情報</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- サービス名 -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            サービス名 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name"
                               value="{{ old('name', $connection->name) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 有効状態 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            状態 <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $connection->is_active) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span class="ml-2 text-sm text-gray-900">有効</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" 
                                       name="is_active" 
                                       value="0"
                                       {{ !old('is_active', $connection->is_active) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span class="ml-2 text-sm text-gray-900">無効</span>
                            </label>
                        </div>
                        @error('is_active')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 説明 -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        説明
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="3"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $connection->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- WebSocket設定 -->
            @if($connection->service_type === 'websocket_message')
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">WebSocket設定</h2>
                    
                    <!-- WebSocketサーバーアドレス設定 -->
                    <div class="mb-6">
                        <h3 class="text-md font-medium text-gray-900 mb-3">WebSocketサーバーアドレス</h3>
                        
                        <div class="space-y-4">
                            <!-- サーバー場所選択 -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    WebSocketサーバーの場所 <span class="text-red-500">*</span>
                                </label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" 
                                               name="config[use_localhost]" 
                                               value="1"
                                               {{ old('config.use_localhost', $connection->config['use_localhost'] ?? true) ? 'checked' : '' }}
                                               onchange="toggleServerAddress()"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                        <span class="ml-2 text-sm text-gray-900">localhostを使用（SJT-CPと同じサーバー）</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" 
                                               name="config[use_localhost]" 
                                               value="0"
                                               {{ !old('config.use_localhost', $connection->config['use_localhost'] ?? true) ? 'checked' : '' }}
                                               onchange="toggleServerAddress()"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                        <span class="ml-2 text-sm text-gray-900">指定したアドレスを使用（別サーバー）</span>
                                    </label>
                                </div>
                                @error('config.use_localhost')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- カスタムサーバーアドレス -->
                            <div id="server-address-field" style="display: none;">
                                <label for="server_address" class="block text-sm font-medium text-gray-700 mb-1">
                                    WebSocketサーバーアドレス
                                </label>
                                <input type="text" 
                                       name="config[server_address]" 
                                       id="server_address"
                                       value="{{ old('config.server_address', $connection->config['server_address'] ?? '') }}"
                                       placeholder="例: 192.168.1.100 または websocket.example.com"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <p class="mt-1 text-sm text-gray-500">
                                    別サーバーでWebSocketサーバーを起動した場合のIPアドレスまたはドメイン名
                                </p>
                                @error('config.server_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- プロトコル -->
                        <div>
                            <label for="protocol" class="block text-sm font-medium text-gray-700 mb-1">
                                プロトコル <span class="text-red-500">*</span>
                            </label>
                            <select name="config[protocol]" 
                                    id="protocol" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="ws" {{ old('config.protocol', $connection->config['protocol'] ?? 'ws') === 'ws' ? 'selected' : '' }}>
                                    ws (非暗号化)
                                </option>
                                <option value="wss" {{ old('config.protocol', $connection->config['protocol'] ?? 'ws') === 'wss' ? 'selected' : '' }}>
                                    wss (暗号化)
                                </option>
                            </select>
                            @error('config.protocol')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- デフォルトポート -->
                        <div>
                            <label for="default_port" class="block text-sm font-medium text-gray-700 mb-1">
                                デフォルトポート <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="config[default_port]" 
                                   id="default_port"
                                   value="{{ old('config.default_port', $connection->config['default_port'] ?? 8080) }}"
                                   min="1" 
                                   max="65535"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('config.default_port')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- パス -->
                        <div>
                            <label for="path" class="block text-sm font-medium text-gray-700 mb-1">
                                パス <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="config[path]" 
                                   id="path"
                                   value="{{ old('config.path', $connection->config['path'] ?? '/message') }}"
                                   placeholder="/message"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('config.path')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- タイムアウト -->
                        <div>
                            <label for="timeout" class="block text-sm font-medium text-gray-700 mb-1">
                                タイムアウト（秒） <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="config[timeout]" 
                                   id="timeout"
                                   value="{{ old('config.timeout', $connection->config['timeout'] ?? 10) }}"
                                   min="1" 
                                   max="300"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('config.timeout')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- リトライ回数 -->
                        <div>
                            <label for="retry_count" class="block text-sm font-medium text-gray-700 mb-1">
                                リトライ回数 <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="config[retry_count]" 
                                   id="retry_count"
                                   value="{{ old('config.retry_count', $connection->config['retry_count'] ?? 3) }}"
                                   min="0" 
                                   max="10"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('config.retry_count')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- リトライ間隔 -->
                        <div>
                            <label for="retry_delay" class="block text-sm font-medium text-gray-700 mb-1">
                                リトライ間隔（ミリ秒） <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="config[retry_delay]" 
                                   id="retry_delay"
                                   value="{{ old('config.retry_delay', $connection->config['retry_delay'] ?? 1000) }}"
                                   min="0" 
                                   max="60000"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('config.retry_delay')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- 保存ボタン -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.external-connections.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                キャンセル
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                設定を保存
            </button>
        </div>
    </form>

    <script>
        // サーバーアドレス設定の表示切替
        function toggleServerAddress() {
            const useLocalhost = document.querySelector('input[name="config[use_localhost]"]:checked').value;
            const serverAddressField = document.getElementById('server-address-field');
            
            if (useLocalhost === '0') {
                serverAddressField.style.display = 'block';
            } else {
                serverAddressField.style.display = 'none';
            }
        }

        // 初期状態で表示切替
        document.addEventListener('DOMContentLoaded', function() {
            toggleServerAddress();
        });
    </script>
@endsection