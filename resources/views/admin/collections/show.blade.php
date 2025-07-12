@extends('layouts.admin')

@section('title', 'コレクション詳細 - SJT-CP')

@php
    $pageTitle = $collection->display_name;
    $pageDescription = 'コレクション詳細情報';
    $pageActions = [
        [
            'label' => 'コンテンツを管理',
            'url' => route('admin.collections.contents.index', $collection),
            'type' => 'primary',
            'icon' => '<svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>'
        ],
        [
            'label' => '編集',
            'url' => route('admin.collections.edit', $collection),
            'type' => 'secondary',
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
                            <span class="text-blue-500">{{ $collection->fields->count() }}</span>
                        </div>
                        <div class="text-sm font-medium text-gray-900">フィールド数</div>
                        <div class="text-xs text-gray-500 mt-1">
                            登録されているフィールド項目
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- フィールド一覧 -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">フィールド一覧</h3>
                <button type="button" onclick="showAddFieldModal()"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    フィールドを追加
                </button>
            </div>
            
            @if($collection->fields->count() > 0)
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
                                    @if($collection->is_player_managed)
                                        進捗
                                    @else
                                        コンテンツ数
                                    @endif
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    操作
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($collection->fields as $field)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $field->name }}</div>
                                        @if($field->is_required)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                                必須
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $field->content_type_display_name }}
                                        </span>
                                        @if($field->max_length)
                                            <div class="text-xs text-gray-500 mt-1">
                                                最大{{ $field->max_length }}文字
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        表示順: {{ $field->sort_order }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        @if($collection->is_player_managed)
                                            @php $rate = $field->completion_rate; @endphp
                                            <div class="flex items-center">
                                                <span class="text-sm font-medium">{{ $rate['completed'] }}/{{ $rate['total'] }}</span>
                                                <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $rate['total'] > 0 ? ($rate['completed'] / $rate['total'] * 100) : 0 }}%"></div>
                                                </div>
                                            </div>
                                        @else
                                            {{ $field->contents->count() }}件
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="showEditFieldModal({{ $field->id }})" class="text-green-600 hover:text-green-900">編集</button>
                                            <button type="button" onclick="deleteField({{ $field->id }})" class="text-red-600 hover:text-red-900">削除</button>
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
                    <h3 class="mt-2 text-sm font-medium text-gray-900">フィールドがありません</h3>
                    <p class="mt-1 text-sm text-gray-500">最初のフィールドを追加しましょう。</p>
                    <div class="mt-6">
                        <button type="button" onclick="showAddFieldModal()"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            フィールドを追加
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
                                    タイプ
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    値
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    状態
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
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($accessControl->type === 'ip_whitelist') bg-blue-100 text-blue-800
                                            @elseif($accessControl->type === 'api_token') bg-green-100 text-green-800
                                            @else bg-purple-100 text-purple-800 @endif">
                                            {{ $accessControl->type_display_name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $accessControl->display_value }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($accessControl->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                有効
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                無効
                                            </span>
                                        @endif
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
                        <label for="access_type" class="block text-sm font-medium text-gray-700 mb-1">
                            制限タイプ <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="access_type" required onchange="onAccessTypeChange()"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">選択してください</option>
                            <option value="ip_whitelist">IP許可</option>
                            <option value="api_token">APIトークン</option>
                            <option value="token_required">トークン必須</option>
                        </select>
                    </div>
                    
                    <div id="ip_field" class="hidden">
                        <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-1">
                            IPアドレス <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="value" id="ip_address"
                               placeholder="例: 192.168.1.100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div id="token_field" class="hidden">
                        <label for="api_token" class="block text-sm font-medium text-gray-700 mb-1">
                            APIトークン <span class="text-red-500">*</span>
                        </label>
                        <select name="value" id="api_token"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">選択してください</option>
                            @foreach($apiTokens as $token)
                                <option value="{{ $token->id }}">{{ $token->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div id="token_required_field" class="hidden">
                        <div class="text-sm text-gray-600">
                            任意のAPIトークンでのアクセスを許可します
                        </div>
                        <input type="hidden" name="value" id="token_required_value" value="any">
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

    <!-- フィールド追加・編集モーダル -->
    <div id="field-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-full max-w-2xl mx-4">
            <form id="field-form">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 id="field-modal-title" class="text-lg font-medium text-gray-900">フィールドを追加</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label for="field_name" class="block text-sm font-medium text-gray-700 mb-1">
                            名前 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="field_name" required
                               placeholder="例: score"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">半角英数字、アンダースコア(_)、ハイフン(-)のみ使用可能</p>
                        <div id="field_name_error" class="mt-1 text-sm text-red-600 hidden"></div>
                    </div>

                    <div>
                        <label for="content_type" class="block text-sm font-medium text-gray-700 mb-1">
                            コンテンツタイプ <span class="text-red-500">*</span>
                        </label>
                        <select name="content_type" id="content_type" required onchange="onContentTypeChange()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">選択してください</option>
                            <option value="string">文字列（最大255文字）</option>
                            <option value="text">テキスト（最大5000文字）</option>
                            <option value="boolean">真偽値（はい/いいえ）</option>
                            <option value="resource">リソース</option>
                            <option value="date">日付</option>
                            <option value="time">時刻</option>
                        </select>
                        <div id="content_type_error" class="mt-1 text-sm text-red-600 hidden"></div>
                    </div>

                    <div id="max_length_field" class="hidden">
                        <label for="max_length" class="block text-sm font-medium text-gray-700 mb-1">最大文字数</label>
                        <input type="number" name="max_length" id="max_length" min="1" max="65535"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">未設定の場合はデフォルト値が使用されます</p>
                        <div id="max_length_error" class="mt-1 text-sm text-red-600 hidden"></div>
                    </div>

                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">表示順序</label>
                        <input type="number" name="sort_order" id="sort_order" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">未設定の場合は最後に追加されます</p>
                        <div id="sort_order_error" class="mt-1 text-sm text-red-600 hidden"></div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="is_required" id="is_required" value="1"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="is_required" class="text-sm font-medium text-gray-700">必須項目</label>
                            <p class="text-sm text-gray-500">このコンテンツを必須入力項目にする</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="hideFieldModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        キャンセル
                    </button>
                    <button type="submit" id="field-submit-btn"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        追加
                    </button>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
<script>
let editingFieldId = null;
const collectionId = {{ $collection->id }};

function showAddFieldModal() {
    editingFieldId = null;
    document.getElementById('field-modal-title').textContent = 'フィールドを追加';
    document.getElementById('field-submit-btn').textContent = '追加';
    resetFieldForm();
    document.getElementById('field-modal').classList.remove('hidden');
}

function showEditFieldModal(fieldId) {
    editingFieldId = fieldId;
    document.getElementById('field-modal-title').textContent = 'フィールドを編集';
    document.getElementById('field-submit-btn').textContent = '更新';
    
    // 既存データをサーバーから取得
    fetch(`/admin/collections/${collectionId}/fields/${fieldId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const field = data.field;
                
                // フォームに値を設定
                document.getElementById('field_name').value = field.name || '';
                document.getElementById('content_type').value = field.content_type || '';
                document.getElementById('max_length').value = field.max_length || '';
                document.getElementById('sort_order').value = field.sort_order || '';
                document.getElementById('is_required').checked = field.is_required || false;
                
                // コンテンツタイプに応じてmax_lengthフィールドの表示を切り替え
                onContentTypeChange();
                
                document.getElementById('field-modal').classList.remove('hidden');
            } else {
                alert('フィールドデータの取得に失敗しました。');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('通信エラーが発生しました。');
        });
}

function hideFieldModal() {
    document.getElementById('field-modal').classList.add('hidden');
    resetFieldForm();
    editingFieldId = null;
}

function resetFieldForm() {
    document.getElementById('field-form').reset();
    document.getElementById('max_length_field').classList.add('hidden');
    clearErrors();
}

function onContentTypeChange() {
    const contentType = document.getElementById('content_type').value;
    const maxLengthField = document.getElementById('max_length_field');
    const maxLengthInput = document.getElementById('max_length');
    
    if (contentType === 'string' || contentType === 'text') {
        maxLengthField.classList.remove('hidden');
        if (contentType === 'string') {
            maxLengthInput.placeholder = 'デフォルト: 255';
            maxLengthInput.value = '';
        } else {
            maxLengthInput.placeholder = 'デフォルト: 5000';
            maxLengthInput.value = '';
        }
    } else {
        maxLengthField.classList.add('hidden');
        maxLengthInput.value = '';
    }
}

function clearErrors() {
    const errorFields = ['field_name_error', 'content_type_error', 'max_length_error', 'sort_order_error'];
    errorFields.forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.classList.add('hidden');
            element.textContent = '';
        }
    });
}

function showError(fieldName, message) {
    const errorElement = document.getElementById(fieldName + '_error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
    }
}

document.getElementById('field-form').addEventListener('submit', function(e) {
    e.preventDefault();
    clearErrors();
    
    const formData = new FormData(this);
    const url = editingFieldId 
        ? `/admin/collections/${collectionId}/fields/${editingFieldId}`
        : `/admin/collections/${collectionId}/fields`;
    
    const method = editingFieldId ? 'PUT' : 'POST';
    
    if (editingFieldId) {
        formData.append('_method', 'PUT');
    }
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideFieldModal();
            location.reload(); // 簡単な実装として画面をリロード
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    showError(field, data.errors[field][0]);
                });
            } else {
                alert(data.message || 'エラーが発生しました。');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('通信エラーが発生しました。');
    });
});

function deleteField(fieldId) {
    if (!confirm('このフィールドを削除しますか？関連するコンテンツも全て削除されます。')) {
        return;
    }
    
    fetch(`/admin/collections/${collectionId}/fields/${fieldId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '削除に失敗しました。');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('通信エラーが発生しました。');
    });
}

function onAccessTypeChange() {
    const type = document.getElementById('access_type').value;
    const ipField = document.getElementById('ip_field');
    const tokenField = document.getElementById('token_field');
    const tokenRequiredField = document.getElementById('token_required_field');
    
    // すべて非表示にする
    ipField.classList.add('hidden');
    tokenField.classList.add('hidden');
    tokenRequiredField.classList.add('hidden');
    
    // 必要なフィールドのみ表示
    switch (type) {
        case 'ip_whitelist':
            ipField.classList.remove('hidden');
            document.getElementById('ip_address').setAttribute('name', 'value');
            break;
        case 'api_token':
            tokenField.classList.remove('hidden');
            document.getElementById('api_token').setAttribute('name', 'value');
            break;
        case 'token_required':
            tokenRequiredField.classList.remove('hidden');
            document.getElementById('token_required_value').setAttribute('name', 'value');
            break;
    }
}

function showAddAccessControlModal() {
    document.getElementById('add-access-control-modal').classList.remove('hidden');
}

function hideAddAccessControlModal() {
    document.getElementById('add-access-control-modal').classList.add('hidden');
    document.getElementById('access_type').value = '';
    document.getElementById('ip_address').value = '';
    document.getElementById('api_token').value = '';
    onAccessTypeChange(); // フィールドをリセット
}

// モーダルの外側をクリックしたときに閉じる
document.getElementById('add-access-control-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideAddAccessControlModal();
    }
});

document.getElementById('field-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideFieldModal();
    }
});
</script>
@endpush

<!-- データ分析セクション -->
<div class="bg-white rounded-lg shadow overflow-hidden mt-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">データ分析</h3>
    </div>
    <div class="px-6 py-4">
        <!-- 概要統計 -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="text-center p-4 border border-gray-200 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ $analytics['total_records'] }}</div>
                <div class="text-sm text-gray-500">総レコード数</div>
            </div>
            <div class="text-center p-4 border border-gray-200 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ $analytics['completion_rate'] }}%</div>
                <div class="text-sm text-gray-500">完了率</div>
            </div>
            <div class="text-center p-4 border border-gray-200 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">{{ count($collection->fields) }}</div>
                <div class="text-sm text-gray-500">フィールド数</div>
            </div>
            <div class="text-center p-4 border border-gray-200 rounded-lg">
                <div class="text-2xl font-bold text-orange-600">{{ $collection->contents()->count() }}</div>
                <div class="text-sm text-gray-500">総コンテンツ数</div>
            </div>
        </div>

        <!-- フィールド統計 -->
        @if(count($analytics['field_stats']) > 0)
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-900 mb-3">フィールド別統計</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">フィールド名</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイプ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">コンテンツ数</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">完了率</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($analytics['field_stats'] as $stat)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $stat['field_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat['field_type'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat['content_count'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $stat['completion_rate'] }}%"></div>
                                    </div>
                                    <span>{{ $stat['completion_rate'] }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 最近のアクティビティ -->
            @if(count($analytics['recent_activity']) > 0)
            <div>
                <h4 class="text-md font-medium text-gray-900 mb-3">最近のアクティビティ</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="space-y-2">
                        @foreach($analytics['recent_activity'] as $activity)
                        <div class="text-sm">
                            <span class="font-medium">{{ $activity['field_name'] }}</span>
                            @if($activity['competition_name'] || $activity['player_name'])
                                <span class="text-gray-500">
                                    -
                                    @if($activity['competition_name']){{ $activity['competition_name'] }}@endif
                                    @if($activity['player_name']){{ $activity['competition_name'] ? ' / ' : '' }}{{ $activity['player_name'] }}@endif
                                </span>
                            @endif
                            <span class="text-gray-400 float-right">{{ $activity['updated_at'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- 大会別統計 -->
            @if($collection->is_competition_managed && count($analytics['competition_stats']) > 0)
            <div>
                <h4 class="text-md font-medium text-gray-900 mb-3">大会別統計（上位10件）</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="space-y-2">
                        @foreach($analytics['competition_stats'] as $stat)
                        <div class="flex justify-between text-sm">
                            <span>{{ $stat['competition_name'] }}</span>
                            <span class="font-medium">{{ $stat['content_count'] }} 件</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- 選手別統計 -->
            @if($collection->is_player_managed && count($analytics['player_stats']) > 0)
            <div>
                <h4 class="text-md font-medium text-gray-900 mb-3">選手別統計（上位10件）</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="space-y-2">
                        @foreach($analytics['player_stats'] as $stat)
                        <div class="flex justify-between text-sm">
                            <span>{{ $stat['player_name'] }}</span>
                            <span class="font-medium">{{ $stat['content_count'] }} 件</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection