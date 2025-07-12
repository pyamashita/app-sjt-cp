@extends('layouts.admin')

@section('title', 'ガイドページ編集 - SJT-CP')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">ガイドページ編集</h1>
                <p class="mt-2 text-sm text-gray-600">{{ $guidePage->title }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.guide-pages.preview', $guidePage) }}" 
                   target="_blank"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    プレビュー
                </a>
                @if(!$guidePage->is_active)
                    <form method="POST" action="{{ route('admin.guide-pages.activate', $guidePage) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                                onclick="return confirm('このページを有効化しますか？同じ大会の他のページは無効になります。')">
                            有効化
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- 基本情報編集 -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
        </div>
        <form method="POST" action="{{ route('admin.guide-pages.update', $guidePage) }}" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="competition_id" class="block text-sm font-medium text-gray-700 mb-2">
                        大会 <span class="text-red-500">*</span>
                    </label>
                    <select name="competition_id" id="competition_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" required>
                        @foreach($competitions as $competition)
                            <option value="{{ $competition->id }}" {{ $guidePage->competition_id == $competition->id ? 'selected' : '' }}>
                                {{ $competition->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        ページタイトル <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="title" 
                           id="title" 
                           value="{{ old('title', $guidePage->title) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" 
                           required>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" 
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    基本情報を更新
                </button>
            </div>
        </form>
    </div>

    <!-- コンテンツ編集 -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-medium text-gray-900">コンテンツ</h2>
                <button onclick="addSection()" 
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    セクション追加
                </button>
            </div>
        </div>
        <div class="p-6">
            <div id="sections-container">
                @foreach($guidePage->sections as $section)
                    <div class="section-item border border-gray-200 rounded-lg mb-4" data-section-id="{{ $section->id }}">
                        <div class="bg-gray-50 px-4 py-3 flex justify-between items-center">
                            <h3 class="font-medium text-gray-900">{{ $section->title }}</h3>
                            <div class="flex space-x-2">
                                <button onclick="addGroup({{ $section->id }})" 
                                        class="text-blue-600 hover:text-blue-800 text-sm">
                                    グループ追加
                                </button>
                                <button onclick="deleteSection({{ $section->id }})" 
                                        class="text-red-600 hover:text-red-800 text-sm">
                                    削除
                                </button>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="groups-container" id="groups-{{ $section->id }}">
                                @foreach($section->groups as $group)
                                    <div class="group-item border-l-4 border-blue-500 pl-4 mb-4" data-group-id="{{ $group->id }}">
                                        <div class="flex justify-between items-center mb-2">
                                            <h4 class="font-medium text-gray-800">{{ $group->title }}</h4>
                                            <div class="flex space-x-2">
                                                <button onclick="showAddItemModal({{ $group->id }})" 
                                                        class="text-green-600 hover:text-green-800 text-sm">
                                                    アイテム追加
                                                </button>
                                                <button onclick="deleteGroup({{ $group->id }})" 
                                                        class="text-red-600 hover:text-red-800 text-sm">
                                                    削除
                                                </button>
                                            </div>
                                        </div>
                                        <div class="items-container" id="items-{{ $group->id }}">
                                            @foreach($group->items as $item)
                                                <div class="item flex justify-between items-center py-2 px-3 bg-gray-50 rounded mb-2" data-item-id="{{ $item->id }}">
                                                    <div class="flex items-center space-x-3">
                                                        @php
                                                            $typeColors = [
                                                                'resource' => 'bg-blue-100 text-blue-800',
                                                                'link' => 'bg-green-100 text-green-800',
                                                                'text' => 'bg-yellow-100 text-yellow-800',
                                                                'collection' => 'bg-purple-100 text-purple-800'
                                                            ];
                                                        @endphp
                                                        <span class="text-xs px-2 py-1 rounded {{ $typeColors[$item->type] ?? 'bg-gray-100 text-gray-800' }}">
                                                            {{ $item->getTypeDisplayName() }}
                                                        </span>
                                                        <span class="text-sm text-gray-900">{{ $item->title }}</span>
                                                        @if($item->type === 'resource' && $item->resource)
                                                            <span class="text-xs text-gray-500">({{ $item->resource->name }})</span>
                                                        @elseif($item->type === 'link')
                                                            <span class="text-xs text-gray-500">({{ $item->url }})</span>
                                                        @elseif($item->type === 'text')
                                                            <span class="text-xs text-gray-500">({{ $item->getTruncatedTextContent(50) }})</span>
                                                            @if($item->show_copy_button)
                                                                <span class="text-xs text-blue-500">[コピー可]</span>
                                                            @endif
                                                        @elseif($item->type === 'collection' && $item->collection)
                                                            <span class="text-xs text-gray-500">({{ $item->collection->display_name }})</span>
                                                        @endif
                                                    </div>
                                                    <button onclick="deleteItem({{ $item->id }})" 
                                                            class="text-red-600 hover:text-red-800 text-sm">
                                                        削除
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- アイテム追加モーダル -->
    <div id="addItemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-medium text-gray-900 mb-4">アイテム追加</h3>
            <form id="addItemForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">タイプ</label>
                    <select id="itemType" class="w-full px-3 py-2 border border-gray-300 rounded-md" onchange="toggleItemFields()">
                        <option value="link">リンク</option>
                        <option value="resource">リソース</option>
                        <option value="text">テキスト</option>
                        <option value="collection">コレクション</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">タイトル</label>
                    <input type="text" id="itemTitle" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                
                <div id="urlField" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">URL</label>
                    <input type="url" id="itemUrl" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div id="resourceField" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">リソース</label>
                    <div class="border border-gray-300 rounded-md p-2 bg-gray-50">
                        <button type="button" onclick="openResourceExplorer()" 
                                class="w-full px-3 py-2 text-left bg-white border border-gray-300 rounded-md hover:bg-gray-50 flex items-center justify-between">
                            <span id="selectedResourceName" class="text-gray-500">リソースを選択してください</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                        </button>
                        <input type="hidden" id="itemResource" name="resource_id">
                    </div>
                </div>
                
                <div id="textField" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">テキスト内容（255文字まで）</label>
                    <textarea id="itemText" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md"
                              rows="3"
                              maxlength="255"
                              placeholder="表示するテキストを入力してください"></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <span id="textLength">0</span>/255文字
                    </p>
                    <div class="mt-2">
                        <label class="flex items-center">
                            <input type="checkbox" id="itemCopyButton" class="mr-2">
                            <span class="text-sm text-gray-700">コピーボタンを表示</span>
                        </label>
                    </div>
                </div>
                
                <div id="collectionField" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">コレクション</label>
                    <div class="border border-gray-300 rounded-md p-2 bg-gray-50">
                        <button type="button" onclick="showCollectionModal()" 
                                class="w-full px-3 py-2 text-left bg-white border border-gray-300 rounded-md hover:bg-gray-50 flex items-center justify-between">
                            <span id="selectedCollectionName" class="text-gray-500">コレクションを選択してください</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </button>
                        <input type="hidden" id="itemCollection" name="collection_id">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="itemNewTab" checked class="mr-2">
                        <span class="text-sm text-gray-700">新しいタブで開く</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeAddItemModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        キャンセル
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-md text-sm font-medium hover:bg-purple-700">
                        追加
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- リソースエクスプローラーモーダル -->
    <div id="resourceExplorerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-full max-w-4xl max-h-[80vh] flex flex-col">
            <div class="p-4 border-b border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">リソースを選択</h3>
                    <button type="button" onclick="closeResourceExplorer()" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- 検索・フィルター -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="md:col-span-2">
                        <input type="text" 
                               id="resourceSearch" 
                               placeholder="ファイル名で検索..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                               onkeyup="filterResources()">
                    </div>
                    <div>
                        <select id="resourceTypeFilter" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                onchange="filterResources()">
                            <option value="">すべてのタイプ</option>
                            <option value="application/pdf">PDF</option>
                            <option value="image/">画像</option>
                            <option value="text/">テキスト</option>
                            <option value="application/">アプリケーション</option>
                            <option value="video/">動画</option>
                            <option value="audio/">音声</option>
                        </select>
                    </div>
                    <div>
                        <select id="resourceDateFilter" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                onchange="filterResources()">
                            <option value="">すべての期間</option>
                            <option value="today">今日</option>
                            <option value="week">今週</option>
                            <option value="month">今月</option>
                            <option value="year">今年</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- リソース一覧 -->
            <div class="flex-1 overflow-y-auto p-4">
                <div id="resourceList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($resources as $resource)
                        <div class="resource-item border border-gray-200 rounded-lg p-3 hover:bg-gray-50 cursor-pointer transition-colors"
                             data-resource-id="{{ $resource->id }}"
                             data-resource-name="{{ $resource->name }}"
                             data-resource-type="{{ $resource->mime_type }}"
                             data-resource-ext="{{ pathinfo($resource->original_name, PATHINFO_EXTENSION) }}"
                             data-resource-date="{{ $resource->created_at->format('Y-m-d') }}"
                             onclick="selectResource({{ $resource->id }}, '{{ $resource->name }}')">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    @php
                                        $iconClass = 'text-gray-400';
                                        if (str_contains($resource->mime_type, 'pdf')) {
                                            $iconClass = 'text-red-500';
                                        } elseif (str_contains($resource->mime_type, 'image')) {
                                            $iconClass = 'text-blue-500';
                                        } elseif (str_contains($resource->mime_type, 'text')) {
                                            $iconClass = 'text-green-500';
                                        }
                                    @endphp
                                    <svg class="w-10 h-10 {{ $iconClass }}" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 2v6h6V2h4a1 1 0 011 1v18a1 1 0 01-1 1H5a1 1 0 01-1-1V3a1 1 0 011-1h4zm3 0h2v4h-2V2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $resource->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $resource->original_name }}</p>
                                    <div class="flex items-center mt-1 text-xs text-gray-400">
                                        <span>{{ $resource->formatted_size }}</span>
                                        <span class="mx-1">•</span>
                                        <span>{{ $resource->created_at->format('Y/m/d') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div id="noResourcesMessage" class="hidden text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500">該当するリソースが見つかりません</p>
                </div>
            </div>
            
            <!-- フッター -->
            <div class="p-4 border-t border-gray-200 flex justify-between items-center">
                <div id="selectedResourceInfo" class="text-sm text-gray-600">
                    リソースを選択してください
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeResourceExplorer()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        キャンセル
                    </button>
                    <button type="button" 
                            id="confirmResourceButton"
                            onclick="confirmResourceSelection()" 
                            disabled
                            class="px-4 py-2 bg-purple-600 text-white rounded-md text-sm font-medium hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        選択
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let currentGroupId = null;
let selectedResourceId = null;
let selectedResourceName = null;

// リソースエクスプローラー関連
function openResourceExplorer() {
    document.getElementById('resourceExplorerModal').classList.remove('hidden');
    filterResources(); // 初期表示
}

function closeResourceExplorer() {
    document.getElementById('resourceExplorerModal').classList.add('hidden');
    // 選択状態をリセット
    document.querySelectorAll('.resource-item').forEach(item => {
        item.classList.remove('border-purple-500', 'bg-purple-50');
    });
    selectedResourceId = null;
    selectedResourceName = null;
    document.getElementById('confirmResourceButton').disabled = true;
    document.getElementById('selectedResourceInfo').textContent = 'リソースを選択してください';
}

function selectResource(id, name) {
    // 既存の選択をクリア
    document.querySelectorAll('.resource-item').forEach(item => {
        item.classList.remove('border-purple-500', 'bg-purple-50');
    });
    
    // 新しい選択を設定
    const selectedItem = document.querySelector(`[data-resource-id="${id}"]`);
    selectedItem.classList.add('border-purple-500', 'bg-purple-50');
    
    selectedResourceId = id;
    selectedResourceName = name;
    
    document.getElementById('confirmResourceButton').disabled = false;
    document.getElementById('selectedResourceInfo').textContent = `選択中: ${name}`;
}

function confirmResourceSelection() {
    if (selectedResourceId && selectedResourceName) {
        document.getElementById('itemResource').value = selectedResourceId;
        document.getElementById('selectedResourceName').textContent = selectedResourceName;
        document.getElementById('selectedResourceName').classList.remove('text-gray-500');
        document.getElementById('selectedResourceName').classList.add('text-gray-900');
        closeResourceExplorer();
    }
}

function filterResources() {
    const searchTerm = document.getElementById('resourceSearch').value.toLowerCase();
    const typeFilter = document.getElementById('resourceTypeFilter').value;
    const dateFilter = document.getElementById('resourceDateFilter').value;
    
    const items = document.querySelectorAll('.resource-item');
    let visibleCount = 0;
    
    items.forEach(item => {
        const name = item.dataset.resourceName.toLowerCase();
        const type = item.dataset.resourceType;
        const date = new Date(item.dataset.resourceDate);
        
        let showItem = true;
        
        // 名前フィルター
        if (searchTerm && !name.includes(searchTerm)) {
            showItem = false;
        }
        
        // タイプフィルター
        if (typeFilter) {
            if (!type.startsWith(typeFilter)) {
                showItem = false;
            }
        }
        
        // 日付フィルター
        if (dateFilter) {
            const now = new Date();
            const startOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            
            switch(dateFilter) {
                case 'today':
                    if (date < startOfDay) showItem = false;
                    break;
                case 'week':
                    const weekAgo = new Date(startOfDay);
                    weekAgo.setDate(weekAgo.getDate() - 7);
                    if (date < weekAgo) showItem = false;
                    break;
                case 'month':
                    const monthAgo = new Date(startOfDay);
                    monthAgo.setMonth(monthAgo.getMonth() - 1);
                    if (date < monthAgo) showItem = false;
                    break;
                case 'year':
                    const yearAgo = new Date(startOfDay);
                    yearAgo.setFullYear(yearAgo.getFullYear() - 1);
                    if (date < yearAgo) showItem = false;
                    break;
            }
        }
        
        if (showItem) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // 結果なしメッセージの表示/非表示
    const noResultsMessage = document.getElementById('noResourcesMessage');
    if (visibleCount === 0) {
        noResultsMessage.classList.remove('hidden');
    } else {
        noResultsMessage.classList.add('hidden');
    }
}

function addSection() {
    const title = prompt('セクション名を入力してください:');
    if (!title) return;
    
    fetch(`{{ route('admin.guide-pages.sections.add', $guidePage) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ title: title })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function addGroup(sectionId) {
    const title = prompt('グループ名を入力してください:');
    if (!title) return;
    
    fetch(`/admin/guide-page-sections/${sectionId}/groups`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ title: title })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function showAddItemModal(groupId) {
    currentGroupId = groupId;
    document.getElementById('addItemModal').classList.remove('hidden');
}

function closeAddItemModal() {
    document.getElementById('addItemModal').classList.add('hidden');
    document.getElementById('addItemForm').reset();
    currentGroupId = null;
}

function toggleItemFields() {
    const type = document.getElementById('itemType').value;
    const urlField = document.getElementById('urlField');
    const resourceField = document.getElementById('resourceField');
    const textField = document.getElementById('textField');
    const collectionField = document.getElementById('collectionField');
    
    // すべてのフィールドを隠す
    urlField.classList.add('hidden');
    resourceField.classList.add('hidden');
    textField.classList.add('hidden');
    collectionField.classList.add('hidden');
    
    // すべてのrequiredを外す
    document.getElementById('itemUrl').required = false;
    document.getElementById('itemResource').required = false;
    document.getElementById('itemText').required = false;
    document.getElementById('itemCollection').required = false;
    
    // タイプに応じて表示
    switch(type) {
        case 'link':
            urlField.classList.remove('hidden');
            document.getElementById('itemUrl').required = true;
            break;
        case 'resource':
            resourceField.classList.remove('hidden');
            document.getElementById('itemResource').required = true;
            break;
        case 'text':
            textField.classList.remove('hidden');
            document.getElementById('itemText').required = true;
            break;
        case 'collection':
            collectionField.classList.remove('hidden');
            document.getElementById('itemCollection').required = true;
            break;
    }
    
    // 選択をリセット
    if (type !== 'resource') {
        document.getElementById('itemResource').value = '';
        document.getElementById('selectedResourceName').textContent = 'リソースを選択してください';
        document.getElementById('selectedResourceName').classList.add('text-gray-500');
        document.getElementById('selectedResourceName').classList.remove('text-gray-900');
    }
    
    if (type !== 'collection') {
        document.getElementById('itemCollection').value = '';
        document.getElementById('selectedCollectionName').textContent = 'コレクションを選択してください';
        document.getElementById('selectedCollectionName').classList.add('text-gray-500');
        document.getElementById('selectedCollectionName').classList.remove('text-gray-900');
    }
}

document.getElementById('addItemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const type = document.getElementById('itemType').value;
    
    // 各タイプの必須項目チェック
    if (type === 'resource' && !document.getElementById('itemResource').value) {
        alert('リソースを選択してください。');
        return;
    }
    
    if (type === 'text' && !document.getElementById('itemText').value.trim()) {
        alert('テキスト内容を入力してください。');
        return;
    }
    
    if (type === 'collection' && !document.getElementById('itemCollection').value) {
        alert('コレクションを選択してください。');
        return;
    }
    
    const formData = {
        type: type,
        title: document.getElementById('itemTitle').value,
        url: document.getElementById('itemUrl').value,
        resource_id: document.getElementById('itemResource').value,
        text_content: document.getElementById('itemText').value,
        show_copy_button: document.getElementById('itemCopyButton').checked,
        collection_id: document.getElementById('itemCollection').value,
        open_in_new_tab: document.getElementById('itemNewTab').checked
    };
    
    fetch(`/admin/guide-page-groups/${currentGroupId}/items`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeAddItemModal();
            location.reload();
        }
    });
});

function deleteSection(sectionId) {
    if (!confirm('このセクションを削除してもよろしいですか？')) return;
    
    fetch(`/admin/guide-page-sections/${sectionId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function deleteGroup(groupId) {
    if (!confirm('このグループを削除してもよろしいですか？')) return;
    
    fetch(`/admin/guide-page-groups/${groupId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function deleteItem(itemId) {
    if (!confirm('このアイテムを削除してもよろしいですか？')) return;
    
    fetch(`/admin/guide-page-items/${itemId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// テキスト文字数カウント
document.getElementById('itemText').addEventListener('input', function() {
    const length = this.value.length;
    document.getElementById('textLength').textContent = length;
});

// コレクション選択モーダル
function showCollectionModal() {
    document.getElementById('collectionModal').classList.remove('hidden');
}

function closeCollectionModal() {
    document.getElementById('collectionModal').classList.add('hidden');
}

function selectCollection(id, name) {
    document.getElementById('itemCollection').value = id;
    document.getElementById('selectedCollectionName').textContent = name;
    document.getElementById('selectedCollectionName').classList.remove('text-gray-500');
    document.getElementById('selectedCollectionName').classList.add('text-gray-900');
    closeCollectionModal();
}
</script>

<!-- コレクション選択モーダル -->
<div id="collectionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[80vh] flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">コレクションを選択</h3>
            <button type="button" onclick="closeCollectionModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto">
            <div class="space-y-2">
                @foreach($collections as $collection)
                    <div class="p-3 border border-gray-200 rounded-md hover:bg-gray-50 cursor-pointer"
                         onclick="selectCollection({{ $collection->id }}, '{{ $collection->display_name }}')">
                        <div class="font-medium text-gray-900">{{ $collection->display_name }}</div>
                        <div class="text-sm text-gray-500">{{ $collection->name }}</div>
                        <div class="text-xs text-gray-400 mt-1">
                            @if($collection->description)
                                {{ Str::limit($collection->description, 100) }}
                            @else
                                説明なし
                            @endif
                        </div>
                    </div>
                @endforeach
                
                @if($collections->count() === 0)
                    <div class="text-center py-8 text-gray-500">
                        利用可能なコレクションがありません
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endpush