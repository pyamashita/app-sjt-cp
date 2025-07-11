@extends('layouts.admin')

@section('title', 'DBユーザー追加 - SJT-CP')

@php
    $pageTitle = 'DBユーザー追加';
    $pageDescription = $server->hostname . ' にDBユーザーを追加します';
    $breadcrumbs = [
        ['label' => 'サーバ管理', 'url' => route('admin.servers.index')],
        ['label' => $server->hostname, 'url' => route('admin.servers.show', $server)],
        ['label' => 'DBユーザー追加', 'url' => '#']
    ];
@endphp

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">DBユーザー情報入力</h3>
                <p class="text-sm text-gray-600 mt-1">サーバ: {{ $server->hostname }} ({{ $server->type_display_name }})</p>
            </div>
            
            <form method="POST" action="{{ route('admin.database-users.store') }}" class="px-6 py-6 space-y-6">
                @csrf
                <input type="hidden" name="server_id" value="{{ $server->id }}">
                
                <!-- ユーザ名 -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        ユーザ名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required
                           placeholder="例: app_user"
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
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                               placeholder="パスワードを入力してください"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 pr-10">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- 接続許可ホスト -->
                <div>
                    <label for="allowed_hosts" class="block text-sm font-medium text-gray-700 mb-2">
                        接続許可ホスト <span class="text-red-500">*</span>
                    </label>
                    <select id="allowed_hosts" name="allowed_hosts" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">選択してください</option>
                        <option value="%" {{ old('allowed_hosts', '%') === '%' ? 'selected' : '' }}>% (すべてのホスト)</option>
                        <option value="localhost" {{ old('allowed_hosts') === 'localhost' ? 'selected' : '' }}>localhost (ローカルホストのみ)</option>
                        <option value="192.168.%" {{ old('allowed_hosts') === '192.168.%' ? 'selected' : '' }}>192.168.% (プライベートネットワーク)</option>
                        <option value="custom" {{ old('allowed_hosts') && !in_array(old('allowed_hosts'), ['%', 'localhost', '192.168.%']) ? 'selected' : '' }}>カスタム</option>
                    </select>
                    <div id="customHostsField" class="mt-2" style="display: none;">
                        <input type="text" id="custom_hosts" name="custom_hosts" value="{{ old('custom_hosts') }}"
                               placeholder="例: 192.168.1.% または specific.host.com"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    @error('allowed_hosts')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- 説明 -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        説明
                    </label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="DBユーザーの用途や特記事項を入力してください"
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
                        DBユーザーを追加
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // パスワード表示切り替え
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            // アイコンを変更
            const icon = this.querySelector('svg');
            if (type === 'text') {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>';
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            }
        });

        // 接続許可ホストの選択に応じてカスタムフィールドを表示/非表示
        const allowedHostsSelect = document.getElementById('allowed_hosts');
        const customHostsField = document.getElementById('customHostsField');
        const customHostsInput = document.getElementById('custom_hosts');

        function toggleCustomHostsField() {
            if (allowedHostsSelect.value === 'custom') {
                customHostsField.style.display = 'block';
                customHostsInput.required = true;
            } else {
                customHostsField.style.display = 'none';
                customHostsInput.required = false;
                customHostsInput.value = '';
            }
        }

        allowedHostsSelect.addEventListener('change', toggleCustomHostsField);
        
        // ページロード時にも実行
        toggleCustomHostsField();

        // フォーム送信時にカスタム値を設定
        document.querySelector('form').addEventListener('submit', function() {
            if (allowedHostsSelect.value === 'custom') {
                allowedHostsSelect.value = customHostsInput.value;
            }
        });
    </script>
    @endpush
@endsection