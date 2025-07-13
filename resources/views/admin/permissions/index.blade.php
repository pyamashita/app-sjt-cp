@extends('layouts.admin')

@section('title', '権限管理')

@push('styles')
<style>
.permission-table {
    min-width: 800px;
}
.role-header {
    writing-mode: vertical-rl;
    text-orientation: mixed;
}
.permission-checkbox {
    transform: scale(1.2);
}
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- ページヘッダー -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">権限管理</h1>
                <p class="text-gray-600 mt-1">各ロールに対して機能ごとのアクセス権限を設定できます</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.permissions.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    新規権限作成
                </a>
                <button type="button" onclick="setDefaults()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    デフォルト設定
                </button>
            </div>
        </div>
    </div>

    <!-- 権限マトリックス -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form action="{{ route('admin.permissions.update') }}" method="POST" id="permission-form">
            @csrf
            @method('PUT')
            
            <div class="overflow-x-auto">
                <table class="permission-table w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-4 py-3 text-left font-medium text-gray-900">
                                機能
                            </th>
                            @foreach($roles as $role)
                                <th class="border border-gray-300 px-2 py-3 text-center font-medium text-gray-900 min-w-[120px]">
                                    <div class="flex flex-col items-center">
                                        <span class="font-semibold">{{ $role->display_name }}</span>
                                        <span class="text-xs text-gray-500 mt-1">{{ $role->name }}</span>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $permissionGroups = [
                                'admin' => $permissions->filter(function($p) { return str_starts_with($p->url, '/sjt-cp-admin'); })->sortBy('url'),
                                'dashboard' => $permissions->filter(function($p) { return str_starts_with($p->url, '/dashboard'); })->sortBy('url'),
                                'auth' => $permissions->filter(function($p) { return in_array($p->url, ['/login', '/logout']); })->sortBy('url'),
                            ];
                        @endphp

                        @foreach($permissionGroups as $category => $categoryPermissions)
                            @if($categoryPermissions->count() > 0)
                                <tr class="bg-blue-50">
                                    <td colspan="{{ count($roles) + 1 }}" class="border border-gray-300 px-4 py-2 font-semibold text-blue-900">
                                        @switch($category)
                                            @case('admin')
                                                管理画面
                                                @break
                                            @case('dashboard')
                                                ダッシュボード
                                                @break
                                            @case('auth')
                                                認証
                                                @break
                                            @default
                                                その他
                                        @endswitch
                                    </td>
                                </tr>

                                @foreach($categoryPermissions as $permission)
                                    <tr class="hover:bg-gray-50">
                                        <td class="border border-gray-300 px-4 py-3">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="font-medium text-gray-900">{{ $permission->display_name }}</div>
                                                    <div class="text-sm text-blue-600 mt-1 font-mono">{{ $permission->url }}</div>
                                                    @if($permission->description)
                                                        <div class="text-sm text-gray-600 mt-1">{{ $permission->description }}</div>
                                                    @endif
                                                    @if($permission->remarks)
                                                        <div class="text-xs text-gray-500 mt-1">{{ $permission->remarks }}</div>
                                                    @endif
                                                </div>
                                                <div class="flex space-x-1 ml-2">
                                                    <a href="{{ route('admin.permissions.edit', $permission) }}" 
                                                       class="inline-flex items-center p-1 text-gray-400 hover:text-blue-600 transition-colors duration-200"
                                                       title="編集">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        @foreach($roles as $role)
                                            <td class="border border-gray-300 text-center py-3">
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" 
                                                           name="permissions[{{ $role->id }}][]" 
                                                           value="{{ $permission->id }}"
                                                           class="permission-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                           {{ in_array($permission->id, $permissionMatrix[$role->id] ?? []) ? 'checked' : '' }}>
                                                    <span class="ml-2">
                                                        @if(in_array($permission->id, $permissionMatrix[$role->id] ?? []))
                                                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                            </svg>
                                                        @else
                                                            <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                            </svg>
                                                        @endif
                                                    </span>
                                                </label>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    チェックマークをクリックして権限を設定してください
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="resetForm()" 
                            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        リセット
                    </button>
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        権限設定を保存
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- 説明セクション -->
    <div class="bg-blue-50 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-blue-900 mb-4">権限について</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-medium text-blue-800 mb-2">ロールの説明</h3>
                <ul class="space-y-1 text-sm text-blue-700">
                    <li><strong>管理者:</strong> 全ての機能にアクセス可能</li>
                    <li><strong>競技委員:</strong> 競技運営に必要な機能にアクセス可能</li>
                    <li><strong>補佐員:</strong> 限定的な機能のみアクセス可能</li>
                </ul>
            </div>
            <div>
                <h3 class="font-medium text-blue-800 mb-2">注意事項</h3>
                <ul class="space-y-1 text-sm text-blue-700">
                    <li>• URLベースの権限システムを使用しています</li>
                    <li>• 権限変更は即座に反映されます</li>
                    <li>• ワイルドカード(*) により配下のページも含まれます</li>
                    <li>• システム管理権限は慎重に設定してください</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function setDefaults() {
    if (confirm('デフォルト権限設定を適用しますか？現在の設定は上書きされます。')) {
        fetch('{{ route("admin.permissions.set-defaults") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('エラーが発生しました');
        });
    }
}

function resetForm() {
    if (confirm('フォームをリセットしますか？')) {
        location.reload();
    }
}

// チェックボックスのインタラクション改善
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            const icon = this.nextElementSibling.querySelector('svg');
            
            if (this.checked) {
                icon.className = 'w-5 h-5 text-green-500';
                icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>';
            } else {
                icon.className = 'w-5 h-5 text-gray-300';
                icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';
            }
        });
    });
});
</script>
@endpush
@endsection