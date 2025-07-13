@extends('layouts.admin')

@section('title', '権限編集')

@push('styles')
<style>
.form-group {
    margin-bottom: 1.5rem;
}
.help-text {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}
.suggestion-item.active {
    background-color: #dbeafe !important;
}
.suggestion-item:hover {
    background-color: #f0f9ff;
}
#url-autocomplete {
    border: 1px solid #d1d5db;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- ページヘッダー -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">権限編集</h1>
                <p class="text-gray-600 mt-1">{{ $permission->display_name }} の設定を編集します</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.permissions.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    権限管理に戻る
                </a>
            </div>
        </div>
    </div>

    <!-- 権限編集フォーム -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form action="{{ route('admin.permissions.update-permission', $permission) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 権限名 -->
                <div class="form-group">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        権限名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $permission->name) }}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                           placeholder="例: new_feature_access"
                           required>
                    <p class="help-text">システム内部で使用される一意の識別子（英数字とアンダースコアのみ）</p>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 機能タイトル -->
                <div class="form-group">
                    <label for="display_name" class="block text-sm font-medium text-gray-700 mb-2">
                        機能タイトル <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="display_name" 
                           name="display_name" 
                           value="{{ old('display_name', $permission->display_name) }}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('display_name') border-red-500 @enderror"
                           placeholder="例: 新機能アクセス"
                           required>
                    <p class="help-text">権限管理画面に表示される名前</p>
                    @error('display_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- URL -->
            <div class="form-group">
                <label for="url" class="block text-sm font-medium text-gray-700 mb-2">
                    URL <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" 
                           id="url" 
                           name="url" 
                           value="{{ old('url', $permission->url) }}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('url') border-red-500 @enderror"
                           placeholder="例: /sjt-cp-admin/new-feature*"
                           autocomplete="off"
                           required>
                    
                    <!-- オートコンプリート結果表示エリア -->
                    <div id="url-autocomplete" 
                         class="absolute z-50 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                    </div>
                </div>
                <div class="help-text">
                    <p>アクセス制御対象のURLパターン（入力中に利用可能なルートが表示されます）</p>
                    <p class="mt-1"><strong>例:</strong></p>
                    <ul class="list-disc list-inside ml-4 space-y-1">
                        <li><code>/sjt-cp-admin/users*</code> - /sjt-cp-admin/users 配下の全ページ</li>
                        <li><code>/dashboard/reports</code> - 特定のページのみ</li>
                        <li><code>/api/v1/data*</code> - API エンドポイント</li>
                    </ul>
                </div>
                @error('url')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- カテゴリ -->
                <div class="form-group">
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        カテゴリ <span class="text-red-500">*</span>
                    </label>
                    <select id="category" 
                            name="category" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category') border-red-500 @enderror"
                            required>
                        <option value="">カテゴリを選択してください</option>
                        @foreach($categories as $key => $name)
                            <option value="{{ $key }}" {{ old('category', $permission->category) === $key ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="help-text">権限管理画面でのグループ分け</p>
                    @error('category')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 表示順序 -->
                <div class="form-group">
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                        表示順序
                    </label>
                    <input type="number" 
                           id="sort_order" 
                           name="sort_order" 
                           value="{{ old('sort_order', $permission->sort_order) }}"
                           min="0"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('sort_order') border-red-500 @enderror"
                           placeholder="0">
                    <p class="help-text">カテゴリ内での表示順序（小さい数字ほど上に表示）</p>
                    @error('sort_order')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 機能説明 -->
            <div class="form-group">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    機能説明
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                          placeholder="この権限で何ができるかを説明してください">{{ old('description', $permission->description) }}</textarea>
                <p class="help-text">権限の用途や機能について詳しく説明</p>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 機能備考 -->
            <div class="form-group">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">
                    機能備考
                </label>
                <textarea id="remarks" 
                          name="remarks" 
                          rows="2"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('remarks') border-red-500 @enderror"
                          placeholder="追加の注意事項や制限事項があれば記入してください">{{ old('remarks', $permission->remarks) }}</textarea>
                <p class="help-text">注意事項や特記事項</p>
                @error('remarks')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 有効状態 -->
            <div class="form-group">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', $permission->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">
                        権限を有効にする
                    </label>
                </div>
                <p class="help-text ml-6">無効にすると、この権限は権限チェックで無視されます</p>
            </div>

            <!-- ボタン -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.permissions.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                    キャンセル
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    権限を更新
                </button>
            </div>
        </form>
    </div>

    <!-- 使用状況 -->
    @if($permission->roles()->exists())
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">この権限を使用しているロール</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($permission->roles as $role)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">{{ $role->display_name }}</h3>
                                <p class="text-sm text-gray-500">{{ $role->name }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 削除セクション -->
    <div class="bg-red-50 border border-red-200 rounded-xl p-6">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <div class="ml-4 flex-1">
                <h3 class="text-lg font-medium text-red-800">危険な操作</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p class="mb-4">この権限設定を完全に削除します。この操作は取り消せません。</p>
                    
                    @if($permission->roles()->exists())
                        <div class="bg-red-100 border border-red-300 rounded p-3 mb-4">
                            <p class="text-sm text-red-800 font-medium">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                削除できません
                            </p>
                            <p class="text-sm text-red-700 mt-1">
                                この権限は現在 {{ $permission->roles->count() }} 個のロール（{{ $permission->roles->pluck('display_name')->join('、') }}）で使用されています。<br>
                                削除するには、まず全てのロールからこの権限を削除してください。
                            </p>
                        </div>
                    @else
                        <div class="bg-yellow-100 border border-yellow-300 rounded p-3 mb-4">
                            <p class="text-sm text-yellow-800">
                                <strong>削除の影響:</strong>
                            </p>
                            <ul class="text-sm text-yellow-700 mt-1 list-disc list-inside">
                                <li>権限設定が永久に失われます</li>
                                <li>関連するアクセス制御が無効になります</li>
                                <li>この操作は取り消せません</li>
                            </ul>
                        </div>
                        
                        <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="inline" id="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" 
                                    onclick="confirmDelete()"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                この権限設定を削除する
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ヒント -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-6 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">編集時の注意</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>URLパターンを変更すると、アクセス制御の範囲が変わります</li>
                        <li>権限名を変更する場合は、既存のロール設定に影響がないか確認してください</li>
                        <li>権限を無効にすると、その権限が割り当てられたユーザーもアクセスできなくなります</li>
                        <li>カテゴリを変更すると、権限管理画面での表示グループが変わります</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function confirmDelete() {
        if (confirm('本当にこの権限設定を削除しますか？\n\n権限名: {{ $permission->display_name }}\nURL: {{ $permission->url }}\n\nこの操作は取り消せません。')) {
            if (confirm('最終確認：権限設定「{{ $permission->display_name }}」を完全に削除しますか？')) {
                document.getElementById('delete-form').submit();
            }
        }
    }

    // オートコンプリート機能
    document.addEventListener('DOMContentLoaded', function() {
        const urlInput = document.getElementById('url');
        const autocompleteContainer = document.getElementById('url-autocomplete');
        let routes = [];
        let routePatterns = [];
        let debounceTimer = null;

        // ルート情報を取得
        Promise.all([
            fetch('{{ route("admin.api.routes") }}', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            }).then(response => response.json()),
            fetch('{{ route("admin.api.route-patterns") }}', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            }).then(response => response.json())
        ])
        .then(([routesData, patternsData]) => {
            routes = routesData;
            routePatterns = patternsData;
            console.log('Routes loaded:', routes.length);
            console.log('Patterns loaded:', routePatterns.length);
        })
        .catch(error => {
            console.error('Error loading routes:', error);
        });

        // 入力イベントの処理
        urlInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const query = this.value.trim();
                if (query.length < 1) {
                    hideAutocomplete();
                    return;
                }
                showSuggestions(query);
            }, 300);
        });

        // フォーカス時に候補を表示
        urlInput.addEventListener('focus', function() {
            if (this.value.trim().length > 0) {
                showSuggestions(this.value.trim());
            }
        });

        // 外部クリックで非表示
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#url') && !e.target.closest('#url-autocomplete')) {
                hideAutocomplete();
            }
        });

        // キーボードナビゲーション
        urlInput.addEventListener('keydown', function(e) {
            const suggestions = autocompleteContainer.querySelectorAll('.suggestion-item');
            const activeSuggestion = autocompleteContainer.querySelector('.suggestion-item.active');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (activeSuggestion) {
                    activeSuggestion.classList.remove('active');
                    const next = activeSuggestion.nextElementSibling;
                    if (next) {
                        next.classList.add('active');
                    } else {
                        suggestions[0]?.classList.add('active');
                    }
                } else {
                    suggestions[0]?.classList.add('active');
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (activeSuggestion) {
                    activeSuggestion.classList.remove('active');
                    const prev = activeSuggestion.previousElementSibling;
                    if (prev) {
                        prev.classList.add('active');
                    } else {
                        suggestions[suggestions.length - 1]?.classList.add('active');
                    }
                } else {
                    suggestions[suggestions.length - 1]?.classList.add('active');
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeSuggestion) {
                    const url = activeSuggestion.querySelector('.suggestion-url').textContent;
                    urlInput.value = url;
                    hideAutocomplete();
                }
            } else if (e.key === 'Escape') {
                hideAutocomplete();
            }
        });

        function showSuggestions(query) {
            const suggestions = getSuggestions(query);
            
            if (suggestions.length === 0) {
                hideAutocomplete();
                return;
            }

            let html = '';
            
            // 完全一致のルート
            const exactMatches = suggestions.filter(s => s.type === 'exact');
            if (exactMatches.length > 0) {
                html += '<div class="px-3 py-2 bg-gray-50 text-xs font-semibold text-gray-600 border-b">完全一致</div>';
                exactMatches.forEach(suggestion => {
                    html += createSuggestionHtml(suggestion);
                });
            }

            // パターンマッチ
            const patternMatches = suggestions.filter(s => s.type === 'pattern');
            if (patternMatches.length > 0) {
                html += '<div class="px-3 py-2 bg-gray-50 text-xs font-semibold text-gray-600 border-b">パターンマッチ</div>';
                patternMatches.forEach(suggestion => {
                    html += createSuggestionHtml(suggestion);
                });
            }

            // 部分一致のルート
            const partialMatches = suggestions.filter(s => s.type === 'partial');
            if (partialMatches.length > 0) {
                html += '<div class="px-3 py-2 bg-gray-50 text-xs font-semibold text-gray-600 border-b">部分一致</div>';
                partialMatches.forEach(suggestion => {
                    html += createSuggestionHtml(suggestion);
                });
            }

            autocompleteContainer.innerHTML = html;
            autocompleteContainer.classList.remove('hidden');

            // クリックイベントを追加
            autocompleteContainer.querySelectorAll('.suggestion-item').forEach(item => {
                item.addEventListener('click', function() {
                    const url = this.querySelector('.suggestion-url').textContent;
                    urlInput.value = url;
                    hideAutocomplete();
                });
            });
        }

        function getSuggestions(query) {
            const suggestions = [];
            const queryLower = query.toLowerCase();

            // 完全一致
            routes.forEach(route => {
                if (route.uri.toLowerCase() === queryLower) {
                    suggestions.push({
                        type: 'exact',
                        url: route.uri,
                        methods: route.methods,
                        name: route.name,
                        priority: 1
                    });
                }
            });

            // パターン候補
            if (query.length > 2) {
                routePatterns.forEach(pattern => {
                    if (pattern.pattern && pattern.pattern.toLowerCase().includes(queryLower)) {
                        suggestions.push({
                            type: 'pattern',
                            url: pattern.pattern,
                            methods: pattern.methods,
                            name: pattern.name,
                            priority: 2
                        });
                    }
                });
            }

            // 部分一致
            routes.forEach(route => {
                if (route.uri.toLowerCase().includes(queryLower) && route.uri.toLowerCase() !== queryLower) {
                    suggestions.push({
                        type: 'partial',
                        url: route.uri,
                        methods: route.methods,
                        name: route.name,
                        priority: 3
                    });
                }
            });

            // 重複削除と優先順位でソート
            const uniqueSuggestions = suggestions.filter((suggestion, index, self) => 
                index === self.findIndex(s => s.url === suggestion.url)
            );

            return uniqueSuggestions
                .sort((a, b) => a.priority - b.priority)
                .slice(0, 15); // 最大15件
        }

        function createSuggestionHtml(suggestion) {
            const methodBadges = suggestion.methods
                .filter(method => method !== 'HEAD')
                .map(method => {
                    const color = method === 'GET' ? 'bg-green-100 text-green-800' :
                                 method === 'POST' ? 'bg-blue-100 text-blue-800' :
                                 method === 'PUT' ? 'bg-yellow-100 text-yellow-800' :
                                 method === 'DELETE' ? 'bg-red-100 text-red-800' :
                                 'bg-gray-100 text-gray-800';
                    return `<span class="inline-block px-2 py-1 text-xs font-medium rounded ${color}">${method}</span>`;
                })
                .join(' ');

            return `
                <div class="suggestion-item px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="suggestion-url font-mono text-sm text-blue-600">${suggestion.url}</div>
                            ${suggestion.name ? `<div class="text-xs text-gray-500 mt-1">${suggestion.name}</div>` : ''}
                        </div>
                        <div class="flex space-x-1 ml-2">
                            ${methodBadges}
                        </div>
                    </div>
                </div>
            `;
        }

        function hideAutocomplete() {
            autocompleteContainer.classList.add('hidden');
            autocompleteContainer.querySelectorAll('.suggestion-item').forEach(item => {
                item.classList.remove('active');
            });
        }
    });
    </script>
    @endpush
</div>
@endsection