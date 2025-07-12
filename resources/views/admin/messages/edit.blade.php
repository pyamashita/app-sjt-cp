@extends('layouts.admin')

@section('title', 'メッセージ編集 - SJT-CP')

@php
    $pageTitle = 'メッセージ編集';
    $pageDescription = 'メッセージ内容を編集します';
@endphp

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('admin.messages.show', $message) }}" 
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

    <form method="POST" action="{{ route('admin.messages.update', $message) }}" class="space-y-6">
        @csrf
        @method('PUT')
        
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
                                   {{ old('send_method', $message->send_method) === $value ? 'checked' : '' }}
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
                       value="{{ old('scheduled_at', $message->scheduled_at ? $message->scheduled_at->format('Y-m-d\TH:i') : '') }}"
                       class="w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('scheduled_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- タイトル -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    タイトル（全角50文字まで）
                </label>
                <input type="text" 
                       name="title" 
                       id="title"
                       value="{{ old('title', $message->title) }}"
                       maxlength="50"
                       placeholder="メッセージのタイトル"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- 本文 -->
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">
                    本文（全角1000文字まで） <span class="text-red-500">*</span>
                </label>
                <textarea name="content" 
                          id="content" 
                          rows="6"
                          maxlength="1000"
                          placeholder="メッセージの本文を入力してください"
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('content', $message->content) }}</textarea>
                <p class="mt-1 text-sm text-gray-500">
                    <span id="content-count">{{ strlen(old('content', $message->content)) }}</span> / 1000文字
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
                       value="{{ old('link', $message->link) }}"
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
                    <span id="selected-resource-name" class="text-sm text-gray-600">
                        @if($message->resource)
                            {{ $message->resource->name }}
                        @else
                            未選択
                        @endif
                    </span>
                    @if($message->resource)
                        <button type="button" 
                                onclick="clearResource()"
                                class="text-red-600 hover:text-red-800 text-sm">
                            クリア
                        </button>
                    @endif
                </div>
                <input type="hidden" 
                       name="resource_id" 
                       id="resource_id" 
                       value="{{ old('resource_id', $message->resource_id) }}">
                @error('resource_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <!-- 選択された画像のプレビュー -->
                <div id="resource-preview" class="mt-3" style="{{ $message->resource ? '' : 'display: none;' }}">
                    <img id="resource-image" 
                         src="{{ $message->resource ? asset('storage/' . $message->resource->file_path) : '' }}" 
                         alt="選択された画像" 
                         class="max-w-xs h-auto rounded border">
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
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="device_ids[]" 
                               value="{{ $device->id }}"
                               {{ in_array($device->id, old('device_ids', $selectedDeviceIds)) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-900">
                            {{ $device->name }}
                            <span class="text-gray-500">({{ $device->ip_address }})</span>
                        </span>
                    </label>
                @endforeach
            </div>
            
            @error('device_ids')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- 更新ボタン -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.messages.show', $message) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                キャンセル
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                メッセージを更新
            </button>
        </div>
    </form>

    <!-- リソース選択モーダル -->
    <div id="resource-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeResourceModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">画像を選択</h3>
                        <button type="button" onclick="closeResourceModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-96 overflow-y-auto">
                        @foreach($resources as $resource)
                            <div class="resource-item cursor-pointer border rounded p-2 hover:bg-gray-50" 
                                 onclick="selectResource({{ $resource->id }}, '{{ $resource->name }}', '{{ asset('storage/' . $resource->file_path) }}')">
                                @if($resource->is_image)
                                    <img src="{{ asset('storage/' . $resource->file_path) }}" 
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
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 文字数カウント
        document.getElementById('content').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('content-count').textContent = count;
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
            
            // 初期文字数カウント
            const content = document.getElementById('content');
            if (content.value) {
                document.getElementById('content-count').textContent = content.value.length;
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

        // リソース選択モーダル
        function openResourceModal() {
            document.getElementById('resource-modal').classList.remove('hidden');
        }

        function closeResourceModal() {
            document.getElementById('resource-modal').classList.add('hidden');
        }

        function selectResource(id, name, url) {
            document.getElementById('resource_id').value = id;
            document.getElementById('selected-resource-name').textContent = name;
            
            // プレビュー表示
            const preview = document.getElementById('resource-preview');
            const image = document.getElementById('resource-image');
            image.src = url;
            preview.style.display = 'block';
            
            closeResourceModal();
        }

        function clearResource() {
            document.getElementById('resource_id').value = '';
            document.getElementById('selected-resource-name').textContent = '未選択';
            document.getElementById('resource-preview').style.display = 'none';
        }
    </script>
@endsection