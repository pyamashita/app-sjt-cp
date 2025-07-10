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
    <div id="addItemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
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
                    <select id="itemResource" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">リソースを選択</option>
                        @foreach($resources as $resource)
                            <option value="{{ $resource->id }}">{{ $resource->name }}</option>
                        @endforeach
                    </select>
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
@endsection

@push('scripts')
<script>
let currentGroupId = null;

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
    }
}

document.getElementById('addItemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        type: document.getElementById('itemType').value,
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