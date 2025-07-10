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
                                                        <span class="text-xs px-2 py-1 rounded {{ $item->type === 'resource' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                            {{ $item->type === 'resource' ? 'リソース' : 'リンク' }}
                                                        </span>
                                                        <span class="text-sm text-gray-900">{{ $item->title }}</span>
                                                        @if($item->type === 'resource' && $item->resource)
                                                            <span class="text-xs text-gray-500">({{ $item->resource->name }})</span>
                                                        @elseif($item->type === 'link')
                                                            <span class="text-xs text-gray-500">({{ $item->url }})</span>
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
                            <option value="image">画像</option>
                            <option value="text">テキスト</option>
                            <option value="application">アプリケーション</option>
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
            if (typeFilter === 'image' && !type.includes('image')) {
                showItem = false;
            } else if (typeFilter !== 'image' && !type.includes(typeFilter)) {
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
    
    if (type === 'resource') {
        urlField.classList.add('hidden');
        resourceField.classList.remove('hidden');
        document.getElementById('itemUrl').required = false;
        document.getElementById('itemResource').required = true;
    } else {
        urlField.classList.remove('hidden');
        resourceField.classList.add('hidden');
        document.getElementById('itemUrl').required = true;
        document.getElementById('itemResource').required = false;
        // リソース選択をリセット
        document.getElementById('itemResource').value = '';
        document.getElementById('selectedResourceName').textContent = 'リソースを選択してください';
        document.getElementById('selectedResourceName').classList.add('text-gray-500');
        document.getElementById('selectedResourceName').classList.remove('text-gray-900');
    }
}

document.getElementById('addItemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const type = document.getElementById('itemType').value;
    
    // リソースタイプの場合、リソースが選択されているか確認
    if (type === 'resource' && !document.getElementById('itemResource').value) {
        alert('リソースを選択してください。');
        return;
    }
    
    const formData = {
        type: type,
        title: document.getElementById('itemTitle').value,
        url: document.getElementById('itemUrl').value,
        resource_id: document.getElementById('itemResource').value,
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
</script>
@endpush