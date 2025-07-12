@extends('layouts.admin')

@section('title', 'メッセージ作成 - SJT-CP')

@php
    use Illuminate\Support\Facades\Storage;
    $pageTitle = 'メッセージ作成';
    $pageDescription = '端末にメッセージを送信します';
@endphp

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('admin.messages.index') }}" 
               class="text-gray-600 hover:text-gray-900">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ $pageDescription }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.messages.store') }}" class="space-y-6">
        @csrf
        
        <div class="bg-white rounded-lg shadow p-6 space-y-6">
            <!-- 送信方法 -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">送信方法 <span class="text-red-500">*</span></label>
                <div class="space-y-2">
                    @foreach(\App\Models\Message::getSendMethods() as $value => $label)
                        <label class="flex items-center">
                            <input type="radio" 
                                   name="send_method" 
                                   value="{{ $value }}"
                                   {{ old('send_method') === $value ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                   onchange="toggleScheduledFields()">
                            <span class="ml-2 text-sm text-gray-900">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('send_method')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- 予約送信日時 -->
            <div id="scheduled-fields" style="display: none;">
                <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-1">
                    送信日時 <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" 
                       name="scheduled_at" 
                       id="scheduled_at"
                       value="{{ old('scheduled_at') }}"
                       class="w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('scheduled_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- タイトル -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    タイトル（全角50文字、最大3行まで）
                </label>
                <textarea name="title" 
                          id="title"
                          rows="3"
                          maxlength="50"
                          placeholder="メッセージのタイトル（改行可能、最大3行）"
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          onkeydown="limitLines(this, 3)"
                          onpaste="setTimeout(() => limitLines(this, 3), 0)">{{ old('title') }}</textarea>
                <p class="mt-1 text-sm text-gray-500">
                    <span id="title-count">0</span> / 50文字、<span id="title-lines">1</span> / 3行
                </p>
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- 本文 -->
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">
                    本文（全角1000文字、最大3行まで） <span class="text-red-500">*</span>
                </label>
                <textarea name="content" 
                          id="content" 
                          rows="3"
                          maxlength="1000"
                          placeholder="メッセージの本文を入力してください（改行可能、最大3行）"
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          onkeydown="limitLines(this, 3)"
                          onpaste="setTimeout(() => limitLines(this, 3), 0)">{{ old('content') }}</textarea>
                <p class="mt-1 text-sm text-gray-500">
                    <span id="content-count">0</span> / 1000文字、<span id="content-lines">1</span> / 3行
                </p>
                @error('content')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- リンク -->
            <div>
                <label for="link" class="block text-sm font-medium text-gray-700 mb-1">
                    リンク
                </label>
                <input type="url" 
                       name="link" 
                       id="link"
                       value="{{ old('link') }}"
                       placeholder="https://example.com"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('link')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- 画像 -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    画像
                </label>
                <div class="flex items-center gap-4">
                    <button type="button" 
                            onclick="openResourceModal()"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        画像を選択
                    </button>
                    <span id="selected-resource-name" class="text-sm text-gray-600">未選択</span>
                </div>
                <input type="hidden" name="resource_id" id="resource_id" value="{{ old('resource_id') }}">
                @error('resource_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <!-- 選択された画像のプレビュー -->
                <div id="resource-preview" class="mt-3" style="display: none;">
                    <img id="resource-image" src="" alt="選択された画像" class="max-w-xs h-auto rounded border">
                </div>
            </div>
        </div>

        <!-- 送信対象 -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <label class="block text-sm font-medium text-gray-700">
                    送信対象 <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <button type="button" 
                            onclick="selectAllDevices()"
                            class="text-sm text-blue-600 hover:text-blue-800">
                        全選択
                    </button>
                    <button type="button" 
                            onclick="clearAllDevices()"
                            class="text-sm text-gray-600 hover:text-gray-800">
                        クリア
                    </button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-64 overflow-y-auto border rounded p-3">
                @foreach($devices as $device)
                    <label class="flex items-center group">
                        <input type="checkbox" 
                               name="device_ids[]" 
                               value="{{ $device->id }}"
                               {{ in_array($device->id, old('device_ids', [])) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-900 flex-1">
                            {{ $device->name }}
                            <span class="text-gray-500">({{ $device->ip_address }})</span>
                        </span>
                        <button type="button" 
                                onclick="testDeviceConnection({{ $device->id }}, '{{ $device->name }}', '{{ $device->ip_address }}')"
                                class="ml-2 text-xs text-blue-600 hover:text-blue-800 opacity-0 group-hover:opacity-100 transition-opacity"
                                title="接続テスト">
                            テスト
                        </button>
                    </label>
                @endforeach
            </div>
            
            @error('device_ids')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- 送信ボタン -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.messages.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                キャンセル
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                メッセージを送信
            </button>
        </div>
    </form>

    <!-- リソースエクスプローラーモーダル -->
    <div id="resource-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-full max-w-4xl max-h-[80vh] flex flex-col">
            <div class="p-4 border-b border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">画像を選択</h3>
                    <button type="button" onclick="closeResourceModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- 検索・フィルター -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2">
                        <input type="text" 
                               id="resourceSearch" 
                               placeholder="ファイル名で検索..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                               onkeyup="filterResources()">
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
                <div id="resourceList" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($resources as $resource)
                        <div class="resource-item border border-gray-200 rounded-lg p-3 hover:bg-gray-50 cursor-pointer transition-colors"
                             data-resource-id="{{ $resource->id }}"
                             data-resource-name="{{ $resource->name }}"
                             data-resource-date="{{ $resource->created_at->format('Y-m-d') }}"
                             onclick="selectModalResource({{ $resource->id }}, '{{ $resource->name }}', '{{ $resource->url }}')">
                            @if($resource->is_image)
                                <img src="{{ $resource->url }}" 
                                     alt="{{ $resource->name }}"
                                     class="w-full h-24 object-cover rounded mb-2">
                            @else
                                <div class="w-full h-24 bg-gray-100 rounded mb-2 flex items-center justify-center">
                                    <svg class="h-8 w-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            @endif
                            <p class="text-sm text-gray-900 truncate">{{ $resource->name }}</p>
                            <p class="text-xs text-gray-500">{{ $resource->created_at->format('Y/m/d') }}</p>
                        </div>
                    @endforeach
                </div>
                
                <div id="noResourcesMessage" class="hidden text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-500">該当する画像が見つかりません</p>
                </div>
            </div>
            
            <!-- フッター -->
            <div class="p-4 border-t border-gray-200 flex justify-between items-center">
                <div id="selectedResourceInfo" class="text-sm text-gray-600">
                    画像を選択してください
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeResourceModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        キャンセル
                    </button>
                    <button type="button" 
                            id="confirmResourceButton"
                            onclick="confirmResourceSelection()" 
                            disabled
                            class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        選択
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 行数制限関数
        function limitLines(textarea, maxLines) {
            const lines = textarea.value.split('\n');
            if (lines.length > maxLines) {
                // 最大行数を超えた場合、余分な行を削除
                textarea.value = lines.slice(0, maxLines).join('\n');
            }
        }

        // 文字数・行数カウント関数
        function updateCounts(textarea, countId, linesId) {
            const charCount = textarea.value.length;
            const lineCount = textarea.value.split('\n').length;
            
            document.getElementById(countId).textContent = charCount;
            document.getElementById(linesId).textContent = lineCount;
        }

        // 文字数・行数カウント
        document.getElementById('title').addEventListener('input', function() {
            updateCounts(this, 'title-count', 'title-lines');
        });

        document.getElementById('content').addEventListener('input', function() {
            updateCounts(this, 'content-count', 'content-lines');
        });

        // 送信方法による表示切替
        function toggleScheduledFields() {
            const scheduledRadio = document.querySelector('input[name="send_method"][value="scheduled"]');
            const scheduledFields = document.getElementById('scheduled-fields');
            
            if (scheduledRadio && scheduledRadio.checked) {
                scheduledFields.style.display = 'block';
            } else {
                scheduledFields.style.display = 'none';
            }
        }

        // 初期状態で表示切替
        document.addEventListener('DOMContentLoaded', function() {
            toggleScheduledFields();
            
            // 初期文字数・行数カウント
            const title = document.getElementById('title');
            const content = document.getElementById('content');
            
            if (title.value) {
                updateCounts(title, 'title-count', 'title-lines');
            }
            if (content.value) {
                updateCounts(content, 'content-count', 'content-lines');
            }
        });

        // 端末選択
        function selectAllDevices() {
            const checkboxes = document.querySelectorAll('input[name="device_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = true);
        }

        function clearAllDevices() {
            const checkboxes = document.querySelectorAll('input[name="device_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = false);
        }

        // リソース選択モーダル関連の変数
        let selectedResourceId = null;
        let selectedResourceName = null;
        let selectedResourceUrl = null;

        // リソース選択モーダル
        function openResourceModal() {
            const modal = document.getElementById('resource-modal');
            modal.classList.remove('hidden');
            filterResources(); // 初期表示
            // body のスクロールを無効化
            document.body.style.overflow = 'hidden';
        }

        function closeResourceModal() {
            const modal = document.getElementById('resource-modal');
            modal.classList.add('hidden');
            // body のスクロールを復元
            document.body.style.overflow = 'auto';
            // 選択状態をリセット
            document.querySelectorAll('.resource-item').forEach(item => {
                item.classList.remove('border-blue-500', 'bg-blue-50');
            });
            selectedResourceId = null;
            selectedResourceName = null;
            selectedResourceUrl = null;
            document.getElementById('confirmResourceButton').disabled = true;
            document.getElementById('selectedResourceInfo').textContent = '画像を選択してください';
        }

        function selectModalResource(id, name, url) {
            // 既存の選択をクリア
            document.querySelectorAll('.resource-item').forEach(item => {
                item.classList.remove('border-blue-500', 'bg-blue-50');
            });
            
            // 新しい選択を設定
            const selectedItem = document.querySelector(`[data-resource-id="${id}"]`);
            selectedItem.classList.add('border-blue-500', 'bg-blue-50');
            
            selectedResourceId = id;
            selectedResourceName = name;
            selectedResourceUrl = url;
            
            document.getElementById('confirmResourceButton').disabled = false;
            document.getElementById('selectedResourceInfo').textContent = `選択中: ${name}`;
        }

        function confirmResourceSelection() {
            if (selectedResourceId && selectedResourceName && selectedResourceUrl) {
                document.getElementById('resource_id').value = selectedResourceId;
                document.getElementById('selected-resource-name').textContent = selectedResourceName;
                
                // プレビュー表示
                const preview = document.getElementById('resource-preview');
                const image = document.getElementById('resource-image');
                image.src = selectedResourceUrl;
                preview.style.display = 'block';
                
                closeResourceModal();
            }
        }

        function filterResources() {
            const searchTerm = document.getElementById('resourceSearch').value.toLowerCase();
            const dateFilter = document.getElementById('resourceDateFilter').value;
            
            const items = document.querySelectorAll('.resource-item');
            let visibleCount = 0;
            
            items.forEach(item => {
                const name = item.dataset.resourceName.toLowerCase();
                const date = new Date(item.dataset.resourceDate);
                
                let showItem = true;
                
                // 名前フィルター
                if (searchTerm && !name.includes(searchTerm)) {
                    showItem = false;
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

        // 端末接続テスト
        async function testDeviceConnection(deviceId, deviceName, deviceIp) {
            try {
                console.log('接続テスト開始:', { deviceId, deviceName, deviceIp });
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                console.log('CSRFトークン:', csrfToken);
                
                const response = await fetch(`/admin/devices/${deviceId}/test-connection`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    }
                });

                console.log('レスポンススタータス:', response.status);
                console.log('レスポンスヘッダー:', Object.fromEntries(response.headers.entries()));

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('エラーレスポンス:', errorText);
                    throw new Error(`HTTP ${response.status}: ${errorText}`);
                }

                const result = await response.json();
                console.log('レスポンス結果:', result);
                
                if (result.success) {
                    alert(`✅ ${deviceName} (${deviceIp})\n接続テストに成功しました。`);
                } else {
                    alert(`❌ ${deviceName} (${deviceIp})\n接続テストに失敗しました。\n\nWebSocketサーバーが起動していない可能性があります。`);
                }
                
            } catch (error) {
                console.error('接続テストエラー:', error);
                alert(`❌ ${deviceName} (${deviceIp})\n接続テストでエラーが発生しました。\n\n${error.message}`);
            }
        }
    </script>
@endsection