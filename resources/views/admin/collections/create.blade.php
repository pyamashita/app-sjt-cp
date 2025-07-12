@extends('layouts.admin')

@section('title', 'コレクション作成 - SJT-CP')

@php
    $pageTitle = 'コレクション作成';
    $pageDescription = '新しいデータコレクションを作成';
    $breadcrumbs = [
        ['label' => 'コレクション一覧', 'url' => route('admin.collections.index')],
        ['label' => '新規作成', 'url' => '']
    ];
@endphp

@section('content')
<form method="POST" action="{{ route('admin.collections.store') }}">
    @csrf
    
    <div class="space-y-6">
        <!-- 基本情報 -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">基本情報</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        コレクション名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           placeholder="例: player_scores"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">半角英数字、アンダースコア(_)、ハイフン(-)のみ使用可能</p>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700 mb-1">表示名</label>
                    <input type="text" name="display_name" id="display_name" value="{{ old('display_name') }}"
                           placeholder="例: 選手スコア管理"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('display_name') border-red-500 @enderror">
                    @error('display_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">備考</label>
                    <textarea name="description" id="description" rows="3" 
                              placeholder="コレクションの用途や説明を入力"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
            </div>
        </div>
        
        <!-- 管理設定 -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">管理設定</h3>
            
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="is_competition_managed" id="is_competition_managed" value="1"
                               {{ old('is_competition_managed') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    </div>
                    <div class="ml-3">
                        <label for="is_competition_managed" class="text-sm font-medium text-gray-700">大会ごとに管理</label>
                        <p class="text-sm text-gray-500">このオプションを有効にすると、大会ごとに異なるコンテンツを管理できます</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="is_player_managed" id="is_player_managed" value="1"
                               {{ old('is_player_managed') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    </div>
                    <div class="ml-3">
                        <label for="is_player_managed" class="text-sm font-medium text-gray-700">選手ごとに管理</label>
                        <p class="text-sm text-gray-500">このオプションを有効にすると、選手ごとにコンテンツを管理できます（自動的に大会ごと管理も有効になります）</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- アクセス制限について -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">アクセス制限</h3>
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">アクセス制限について</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>アクセス制限（IP許可、APIトークン、トークン必須）は、コレクション作成後に詳細画面で設定できます。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- アクション -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.collections.index') }}" 
               class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                キャンセル
            </a>
            <button type="submit" 
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                作成
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
let accessControlIndex = 1;

document.getElementById('add-access-control').addEventListener('click', function() {
    const container = document.getElementById('access-controls');
    const newItem = document.createElement('div');
    newItem.className = 'access-control-item border border-gray-200 rounded-md p-4 mb-4';
    newItem.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">IPアドレス</label>
                <input type="text" name="access_controls[${accessControlIndex}][ip_address]" 
                       placeholder="例: 192.168.1.100"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">説明</label>
                <input type="text" name="access_controls[${accessControlIndex}][description]" 
                       placeholder="例: 管理者用PC"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <button type="button" class="mt-2 text-red-600 hover:text-red-700 text-sm" onclick="removeAccessControl(this)">
            削除
        </button>
    `;
    container.appendChild(newItem);
    accessControlIndex++;
});

function removeAccessControl(button) {
    const item = button.closest('.access-control-item');
    item.remove();
}

// 選手ごと管理にチェックが入った場合、大会ごと管理を自動でチェック
document.getElementById('is_player_managed').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('is_competition_managed').checked = true;
    }
});
</script>
@endpush
@endsection