@extends('layouts.admin')

@section('title', '権限作成')

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
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- ページヘッダー -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">新規権限作成</h1>
                <p class="text-gray-600 mt-1">新しいアクセス権限を追加します</p>
            </div>
            <a href="{{ route('admin.permissions.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                権限管理に戻る
            </a>
        </div>
    </div>

    <!-- 権限作成フォーム -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form action="{{ route('admin.permissions.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 権限名 -->
                <div class="form-group">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        権限名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
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
                           value="{{ old('display_name') }}"
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
                <input type="text" 
                       id="url" 
                       name="url" 
                       value="{{ old('url') }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('url') border-red-500 @enderror"
                       placeholder="例: /sjt-cp-admin/new-feature*"
                       required>
                <div class="help-text">
                    <p>アクセス制御対象のURLパターン</p>
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

            <!-- 機能説明 -->
            <div class="form-group">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    機能説明
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                          placeholder="この権限で何ができるかを説明してください">{{ old('description') }}</textarea>
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
                          placeholder="追加の注意事項や制限事項があれば記入してください">{{ old('remarks') }}</textarea>
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
                           {{ old('is_active', true) ? 'checked' : '' }}
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
                    権限を作成
                </button>
            </div>
        </form>
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
                <h3 class="text-sm font-medium text-blue-800">URLパターンのヒント</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li><strong>ワイルドカード（*）</strong>: 配下の全URLにマッチします</li>
                        <li><strong>完全一致</strong>: 特定のURLのみにマッチします</li>
                        <li><strong>プレフィックス</strong>: スラッシュで終わるパターンは配下URLにマッチします</li>
                        <li>権限設定がないURLは全ユーザーがアクセス可能になります</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection