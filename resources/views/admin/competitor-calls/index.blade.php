@extends('layouts.admin')

@section('title', '選手呼び出し一覧 - SJT-CP')

@php
    $pageTitle = '選手呼び出し一覧';
    $pageDescription = 'WebSocketから受信した選手呼び出し履歴を表示します';
    $breadcrumbs = [
        ['label' => '管理画面', 'url' => route('admin.home')],
        ['label' => '選手呼び出し一覧', 'url' => '']
    ];
@endphp

@section('content')
    <!-- フィルタ -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">フィルタ</h3>
            <form method="GET" action="{{ route('admin.competitor-calls.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="device_id" class="block text-sm font-medium text-gray-700 mb-1">端末ID</label>
                    <select name="device_id" id="device_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">すべて</option>
                        @foreach($filterData['device_ids'] as $deviceId)
                            <option value="{{ $deviceId }}" {{ request('device_id') == $deviceId ? 'selected' : '' }}>
                                {{ $deviceId }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="call_type" class="block text-sm font-medium text-gray-700 mb-1">呼び出し種別</label>
                    <select name="call_type" id="call_type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">すべて</option>
                        @foreach($filterData['call_types'] as $value => $label)
                            <option value="{{ $value }}" {{ request('call_type') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="md:col-span-2 lg:col-span-4 flex justify-between">
                    <div class="flex space-x-3">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            検索
                        </button>
                        <a href="{{ route('admin.competitor-calls.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            リセット
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 統計情報 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow-sm border border-gray-200 rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">総呼び出し数</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ $competitorCalls->total() }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow-sm border border-gray-200 rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">一般呼び出し</div>
                        <div class="text-2xl font-semibold text-gray-900">
                            {{ $competitorCalls->where('call_type', 'general')->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow-sm border border-gray-200 rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">技術的呼び出し</div>
                        <div class="text-2xl font-semibold text-gray-900">
                            {{ $competitorCalls->where('call_type', 'technical')->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 呼び出し一覧テーブル -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    選手呼び出し履歴
                    @if($competitorCalls->total() > 0)
                        <span class="text-sm text-gray-500 ml-2">({{ $competitorCalls->total() }}件)</span>
                    @endif
                </h3>
                @if($competitorCalls->count() > 0)
                    <div class="flex space-x-2">
                        <button onclick="toggleBulkActions()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            一括操作
                        </button>
                    </div>
                @endif
            </div>
        </div>

        @if($competitorCalls->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="select-all" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                端末ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                呼び出し種別
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                呼び出し日時
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">操作</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($competitorCalls as $call)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="selected_calls[]" value="{{ $call->id }}" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded call-checkbox">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $call->device_id }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $call->call_type === 'general' 
                                           ? 'bg-green-100 text-green-800' 
                                           : 'bg-orange-100 text-orange-800' }}">
                                        {{ $call->call_type_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $call->called_at->setTimezone('Asia/Tokyo')->format('Y/m/d H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('admin.competitor-calls.show', $call) }}" 
                                           class="text-blue-600 hover:text-blue-900">詳細</a>
                                        <form method="POST" action="{{ route('admin.competitor-calls.destroy', $call) }}" 
                                              class="inline" onsubmit="return confirm('この呼び出し記録を削除しますか？')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 ml-2">削除</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- 一括操作パネル -->
            <div id="bulk-actions" class="hidden px-6 py-3 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        <span id="selected-count">0</span>件選択中
                    </div>
                    <div class="flex space-x-2">
                        <form method="POST" action="{{ route('admin.competitor-calls.bulk-destroy') }}" 
                              id="bulk-delete-form" class="inline" onsubmit="return confirm('選択した呼び出し記録を削除しますか？')">
                            @csrf
                            <input type="hidden" name="ids" id="bulk-ids">
                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                選択項目を削除
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ページネーション -->
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $competitorCalls->withQueryString()->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">呼び出し履歴がありません</h3>
                <p class="mt-1 text-sm text-gray-500">WebSocketからの選手呼び出しメッセージが受信されると、ここに表示されます。</p>
                <div class="mt-6">
                    <p class="text-xs text-gray-400">
                        WebSocket監視コマンド: <code class="bg-gray-100 px-2 py-1 rounded">php artisan websocket:listen-calls</code>
                    </p>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
// 一括選択・操作の JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const callCheckboxes = document.querySelectorAll('.call-checkbox');
    const bulkActionsPanel = document.getElementById('bulk-actions');
    const selectedCountSpan = document.getElementById('selected-count');
    const bulkIdsInput = document.getElementById('bulk-ids');

    // 全選択/全解除
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            callCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }

    // 個別チェックボックスの変更
    callCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const selectedCheckboxes = document.querySelectorAll('.call-checkbox:checked');
        const selectedCount = selectedCheckboxes.length;
        
        if (selectedCount > 0) {
            bulkActionsPanel.classList.remove('hidden');
            selectedCountSpan.textContent = selectedCount;
            
            // 選択されたIDを収集
            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
            bulkIdsInput.value = JSON.stringify(selectedIds);
        } else {
            bulkActionsPanel.classList.add('hidden');
        }

        // 全選択チェックボックスの状態を更新
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = selectedCount === callCheckboxes.length;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < callCheckboxes.length;
        }
    }
});

function toggleBulkActions() {
    const panel = document.getElementById('bulk-actions');
    panel.classList.toggle('hidden');
}
</script>
@endpush