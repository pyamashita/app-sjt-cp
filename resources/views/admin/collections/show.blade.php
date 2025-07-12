@extends('layouts.admin')

@section('title', 'コレクション詳細 - SJT-CP')

@php
    $pageTitle = $collection->display_name;
    $pageDescription = 'コレクション詳細情報';
    $pageActions = [
        [
            'label' => '編集',
            'url' => route('admin.collections.edit', $collection),
            'type' => 'primary',
            'icon' => '<svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>'
        ]
    ];
    $breadcrumbs = [
        ['label' => 'コレクション一覧', 'url' => route('admin.collections.index')],
        ['label' => $collection->display_name, 'url' => '']
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <!-- 基本情報 -->
        <x-detail-card 
            title="基本情報"
            :data="[
                ['label' => 'コレクション名', 'value' => $collection->name],
                ['label' => '表示名', 'value' => $collection->display_name],
                ['label' => '備考', 'value' => $collection->description ?: '-'],
                ['label' => '大会年度', 'value' => $collection->year ? $collection->year . '年' : '-'],
                ['label' => '作成日', 'value' => $collection->created_at->format('Y年m月d日 H:i')],
                ['label' => '最終更新', 'value' => $collection->updated_at->format('Y年m月d日 H:i')]
            ]" />

        <!-- 管理設定 -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">管理設定</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <div class="text-2xl mb-2">
                            @if($collection->is_competition_managed)
                                <span class="text-green-500">✓</span>
                            @else
                                <span class="text-gray-400">✗</span>
                            @endif
                        </div>
                        <div class="text-sm font-medium text-gray-900">大会ごと管理</div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $collection->is_competition_managed ? '有効' : '無効' }}
                        </div>
                    </div>
                    
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <div class="text-2xl mb-2">
                            @if($collection->is_player_managed)
                                <span class="text-green-500">✓</span>
                            @else
                                <span class="text-gray-400">✗</span>
                            @endif
                        </div>
                        <div class="text-sm font-medium text-gray-900">選手ごと管理</div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $collection->is_player_managed ? '有効' : '無効' }}
                        </div>
                    </div>
                    
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <div class="text-2xl mb-2">
                            <span class="text-blue-500">{{ $collection->contents->count() }}</span>
                        </div>
                        <div class="text-sm font-medium text-gray-900">コンテンツ数</div>
                        <div class="text-xs text-gray-500 mt-1">
                            登録されているコンテンツ項目
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- コンテンツ一覧 -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">コンテンツ一覧</h3>
                <button type="button" 
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    コンテンツを追加
                </button>
            </div>
            
            @if($collection->contents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    名前
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    タイプ
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    設定
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    データ数
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    操作
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($collection->contents as $content)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $content->name }}</div>
                                        @if($content->is_required)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                                必須
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $content->content_type_display_name }}
                                        </span>
                                        @if($content->max_length)
                                            <div class="text-xs text-gray-500 mt-1">
                                                最大{{ $content->max_length }}文字
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        表示順: {{ $content->sort_order }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $content->data->count() }}件
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button type="button" class="text-green-600 hover:text-green-900">編集</button>
                                            <button type="button" class="text-red-600 hover:text-red-900">削除</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">コンテンツがありません</h3>
                    <p class="mt-1 text-sm text-gray-500">最初のコンテンツを追加しましょう。</p>
                    <div class="mt-6">
                        <button type="button" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            コンテンツを追加
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- アクセス制限 -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">アクセス制限</h3>
                <form method="POST" action="{{ route('admin.collections.access-control.add', $collection) }}" class="inline">
                    @csrf
                    <button type="button" onclick="showAddAccessControlModal()" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        制限を追加
                    </button>
                </form>
            </div>
            
            @if($collection->accessControls->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    IPアドレス
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    説明
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    追加日
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    操作
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($collection->accessControls as $accessControl)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $accessControl->ip_address }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $accessControl->description ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $accessControl->created_at->format('Y/m/d H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <form method="POST" action="{{ route('admin.collections.access-control.remove', [$collection, $accessControl]) }}" 
                                              class="inline" onsubmit="return confirm('このアクセス制限を削除しますか？')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-4">
                    <div class="text-center">
                        <div class="inline-flex items-center px-4 py-2 rounded-md bg-green-100 text-green-800">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            すべてのIPアドレスからアクセス可能
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- アクセス制限追加モーダル -->
    <div id="add-access-control-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-full max-w-md mx-4">
            <form method="POST" action="{{ route('admin.collections.access-control.add', $collection) }}">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">アクセス制限を追加</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label for="modal_ip_address" class="block text-sm font-medium text-gray-700 mb-1">
                            IPアドレス <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="ip_address" id="modal_ip_address" required
                               placeholder="例: 192.168.1.100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="modal_description" class="block text-sm font-medium text-gray-700 mb-1">説明</label>
                        <input type="text" name="description" id="modal_description"
                               placeholder="例: 管理者用PC"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="hideAddAccessControlModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        キャンセル
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        追加
                    </button>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
<script>
function showAddAccessControlModal() {
    document.getElementById('add-access-control-modal').classList.remove('hidden');
}

function hideAddAccessControlModal() {
    document.getElementById('add-access-control-modal').classList.add('hidden');
    document.getElementById('modal_ip_address').value = '';
    document.getElementById('modal_description').value = '';
}

// モーダルの外側をクリックしたときに閉じる
document.getElementById('add-access-control-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideAddAccessControlModal();
    }
});
</script>
@endpush
@endsection