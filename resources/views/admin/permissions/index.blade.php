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
                                <th class="border border-gray-300 px-2 py-4 text-center font-medium text-gray-900 min-w-[140px] h-24">
                                    <div class="flex flex-col items-center justify-center h-full">
                                        <span class="font-semibold">{{ $role->display_name }}</span>
                                        <span class="text-xs text-gray-500 mt-1">{{ $role->name }}</span>
                                        <button type="button" 
                                                onclick="setRoleDefaults({{ $role->id }}, '{{ $role->display_name }}')"
                                                class="mt-2 inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded hover:bg-blue-200 transition-colors duration-200"
                                                title="プリセット設定">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            プリセット
                                        </button>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $permissionGroups = $permissions->groupBy('category');
                        @endphp

                        @foreach($permissionGroups as $category => $categoryPermissions)
                            @if($categoryPermissions->count() > 0)
                                <tr class="bg-blue-50">
                                    <td colspan="{{ count($roles) + 1 }}" class="border border-gray-300 px-4 py-2 font-semibold text-blue-900">
                                        {{ $categoryPermissions->first()->category_display_name ?? $category }}
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
                <h3 class="font-medium text-blue-800 mb-2">権限設定の使い方</h3>
                <ul class="space-y-1 text-sm text-blue-700">
                    <li>• <strong>個別設定</strong>: チェックボックスで権限を個別に設定</li>
                    <li>• <strong>プリセット設定</strong>: 各ロールの「プリセット」ボタンで一括設定</li>
                    <li>• <strong>デフォルト設定</strong>: 全ロールに推奨設定を一括適用</li>
                    <li>• <strong>新規権限追加</strong>: 「新規権限作成」で新しいURLパターンを追加</li>
                </ul>
            </div>
            <div>
                <h3 class="font-medium text-blue-800 mb-2">技術的な注意事項</h3>
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
    if (confirm('デフォルト権限設定を適用しますか？\n\n管理者: 全権限\n競技委員: ダッシュボード + ガイド管理\n補佐員: ダッシュボードのみ\n\n現在の設定は上書きされます。')) {
        const button = event.target;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" opacity="0.75"></path></svg>設定中...';
        
        fetch('{{ route("admin.permissions.set-defaults") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('成功', data.message + '\n' + (data.details ? data.details.join('\n') : ''), 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('エラー', data.message || 'エラーが発生しました', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('エラー', 'ネットワークエラーが発生しました', 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }
}

function setRoleDefaults(roleId, roleName) {
    // プリセット選択ダイアログを表示
    showPresetModal(roleId, roleName);
}

function showPresetModal(roleId, roleName) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">${roleName} のデフォルト権限設定</h3>
                <div class="space-y-3" id="preset-options">
                    <div class="text-center">
                        <svg class="animate-spin h-8 w-8 mx-auto text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"></circle>
                            <path fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" opacity="0.75"></path>
                        </svg>
                        <p class="mt-2 text-gray-600">プリセットを読み込み中...</p>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        キャンセル
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // プリセット情報を取得
    fetch('{{ route("admin.api.permissions.presets") }}', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
    })
    .then(response => response.json())
    .then(presets => {
        const optionsContainer = document.getElementById('preset-options');
        optionsContainer.innerHTML = '';
        
        Object.entries(presets).forEach(([key, preset]) => {
            const option = document.createElement('div');
            option.className = 'border border-gray-200 rounded-lg p-4 hover:border-blue-300 cursor-pointer transition-colors';
            option.onclick = () => applyPreset(roleId, key, preset.name);
            option.innerHTML = `
                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="font-medium text-gray-900">${preset.name}</h4>
                        <p class="text-sm text-gray-600 mt-1">${preset.description}</p>
                        <p class="text-xs text-blue-600 mt-2">${preset.permissions_count}個の権限</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            `;
            optionsContainer.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('preset-options').innerHTML = '<p class="text-red-600 text-center">プリセットの読み込みに失敗しました</p>';
    });
    
    // モーダルを閉じる関数をグローバルに設定
    window.closeModal = function() {
        document.body.removeChild(modal);
        delete window.closeModal;
        delete window.applyPreset;
    };
    
    // プリセットを適用する関数
    window.applyPreset = function(roleId, preset, presetName) {
        if (confirm(`${roleName} に「${presetName}」プリセットを適用しますか？`)) {
            fetch(`/sjt-cp-admin/system/permissions/role/${roleId}/set-defaults`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ preset: preset })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('成功', data.message, 'success');
                    closeModal();
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification('エラー', data.message || 'エラーが発生しました', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('エラー', 'ネットワークエラーが発生しました', 'error');
            });
        }
    };
}

function showNotification(title, message, type = 'info') {
    const notification = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-50 border-green-200' : type === 'error' ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-blue-200';
    const textColor = type === 'success' ? 'text-green-800' : type === 'error' ? 'text-red-800' : 'text-blue-800';
    const iconColor = type === 'success' ? 'text-green-400' : type === 'error' ? 'text-red-400' : 'text-blue-400';
    
    notification.className = `fixed top-4 right-4 max-w-sm w-full ${bgColor} border rounded-lg p-4 shadow-lg z-50 transition-all duration-300 transform translate-x-full`;
    notification.innerHTML = `
        <div class="flex">
            <div class="flex-shrink-0">
                ${type === 'success' ? 
                    `<svg class="h-5 w-5 ${iconColor}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>` :
                    `<svg class="h-5 w-5 ${iconColor}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>`
                }
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium ${textColor}">${title}</h3>
                <p class="text-sm ${textColor} mt-1 whitespace-pre-line">${message}</p>
            </div>
            <div class="ml-4 flex-shrink-0">
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="inline-flex ${textColor} hover:opacity-75">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // アニメーション
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // 自動削除
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
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