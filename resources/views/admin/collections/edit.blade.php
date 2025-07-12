@extends('layouts.admin')

@section('title', 'コレクション編集 - SJT-CP')

@php
    $pageTitle = 'コレクション編集';
    $pageDescription = $collection->display_name . ' の編集';
    $breadcrumbs = [
        ['label' => 'コレクション一覧', 'url' => route('admin.collections.index')],
        ['label' => $collection->display_name, 'url' => route('admin.collections.show', $collection)],
        ['label' => '編集', 'url' => '']
    ];
@endphp

@section('content')
<form method="POST" action="{{ route('admin.collections.update', $collection) }}">
    @csrf
    @method('PUT')
    
    <div class="space-y-6">
        <!-- 基本情報 -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">基本情報</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        コレクション名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $collection->name) }}" required
                           placeholder="例: player_scores"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">半角英数字、アンダースコア(_)、ハイフン(-)のみ使用可能</p>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700 mb-1">表示名</label>
                    <input type="text" name="display_name" id="display_name" value="{{ old('display_name', $collection->display_name) }}"
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
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $collection->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">大会年度</label>
                    <input type="number" name="year" id="year" value="{{ old('year', $collection->year) }}"
                           min="1900" max="{{ date('Y') + 10 }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('year') border-red-500 @enderror">
                    @error('year')
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
                               {{ old('is_competition_managed', $collection->is_competition_managed) ? 'checked' : '' }}
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
                               {{ old('is_player_managed', $collection->is_player_managed) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    </div>
                    <div class="ml-3">
                        <label for="is_player_managed" class="text-sm font-medium text-gray-700">選手ごとに管理</label>
                        <p class="text-sm text-gray-500">このオプションを有効にすると、選手ごとにコンテンツを管理できます（自動的に大会ごと管理も有効になります）</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- アクセス制限 -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">アクセス制限</h3>
            <p class="text-sm text-gray-600 mb-4">
                IPアドレス制限を設定しない場合、すべてのIPアドレスからアクセス可能になります
            </p>
            
            <div id="access-controls">
                @forelse($collection->accessControls as $index => $accessControl)
                    <div class="access-control-item border border-gray-200 rounded-md p-4 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">IPアドレス</label>
                                <input type="text" name="access_controls[{{ $index }}][ip_address]" 
                                       value="{{ old("access_controls.{$index}.ip_address", $accessControl->ip_address) }}"
                                       placeholder="例: 192.168.1.100"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">説明</label>
                                <input type="text" name="access_controls[{{ $index }}][description]" 
                                       value="{{ old("access_controls.{$index}.description", $accessControl->description) }}"
                                       placeholder="例: 管理者用PC"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <button type="button" class="mt-2 text-red-600 hover:text-red-700 text-sm" onclick="removeAccessControl(this)">
                            削除
                        </button>
                    </div>
                @empty
                    <div class="access-control-item border border-gray-200 rounded-md p-4 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">IPアドレス</label>
                                <input type="text" name="access_controls[0][ip_address]" 
                                       placeholder="例: 192.168.1.100"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">説明</label>
                                <input type="text" name="access_controls[0][description]" 
                                       placeholder="例: 管理者用PC"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <button type="button" class="mt-2 text-red-600 hover:text-red-700 text-sm" onclick="removeAccessControl(this)">
                            削除
                        </button>
                    </div>
                @endforelse
            </div>
            
            <button type="button" id="add-access-control" 
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                アクセス制限を追加
            </button>
        </div>
        
        <!-- アクション -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.collections.show', $collection) }}" 
               class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                キャンセル
            </a>
            <button type="submit" 
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                更新
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
let accessControlIndex = {{ $collection->accessControls->count() }};

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