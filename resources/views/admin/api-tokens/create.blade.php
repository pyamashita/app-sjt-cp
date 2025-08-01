@extends('layouts.admin')

@section('title', 'APIトークン新規作成 - SJT-CP')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">APIトークン新規作成</h1>
        <p class="mt-2 text-sm text-gray-600">新しいAPIトークンを作成します</p>
    </div>

    <form method="POST" action="{{ route('admin.api-tokens.store') }}">
        @csrf
        
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">エラーがあります</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- 基本情報 -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">基本情報</h3>
            </div>
            <div class="px-6 py-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-field
                        name="name"
                        label="トークン名"
                        type="text"
                        :value="old('name')"
                        placeholder="トークン名を入力"
                        required
                    />
                    
                    <x-form-field
                        name="expires_at"
                        label="有効期限"
                        type="datetime-local"
                        :value="old('expires_at')"
                        help-text="空の場合は無期限"
                    />
                    
                    <x-form-field
                        name="description"
                        label="説明"
                        type="textarea"
                        :value="old('description')"
                        placeholder="このトークンの用途を説明"
                        col-span="2"
                    />
                </div>
            </div>
        </div>

        <!-- 権限設定 -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">権限設定</h3>
            </div>
            <div class="px-6 py-8">
                <div class="space-y-4">
                    <p class="text-sm text-gray-600">このトークンに付与する権限を選択してください</p>
                    
                    @foreach(App\Models\ApiToken::getPermissions() as $key => $label)
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="permissions[]" 
                                   id="permission_{{ $key }}"
                                   value="{{ $key }}"
                                   {{ in_array($key, old('permissions', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <label for="permission_{{ $key }}" class="ml-2 block text-sm text-gray-700">
                                {{ $label }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- アクセス制御 -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">アクセス制御</h3>
            </div>
            <div class="px-6 py-8">
                <div class="space-y-4">
                    <p class="text-sm text-gray-600">このトークンからのアクセスを許可するIPアドレスを指定できます（オプション）</p>
                    
                    <div id="ip-addresses" class="space-y-2">
                        <div class="flex items-center space-x-2 ip-address-row">
                            <input type="text" 
                                   name="allowed_ips[]" 
                                   placeholder="192.168.1.100"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <button type="button" 
                                    onclick="removeIpAddress(this)"
                                    class="bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                削除
                            </button>
                        </div>
                    </div>
                    
                    <button type="button" 
                            onclick="addIpAddress()"
                            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        IPアドレスを追加
                    </button>
                </div>
            </div>
        </div>

        <!-- その他の設定 -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">その他の設定</h3>
            </div>
            <div class="px-6 py-8">
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           id="is_active"
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">
                        有効にする
                    </label>
                </div>
            </div>
        </div>

        <!-- 送信ボタン -->
        <div class="mt-6 flex items-center justify-end space-x-3">
            <a href="{{ route('admin.api-tokens.index') }}" 
               class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                キャンセル
            </a>
            <button type="submit" 
                    class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                作成
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
function addIpAddress() {
    const container = document.getElementById('ip-addresses');
    const newRow = document.createElement('div');
    newRow.className = 'flex items-center space-x-2 ip-address-row';
    newRow.innerHTML = `
        <input type="text" 
               name="allowed_ips[]" 
               placeholder="192.168.1.100"
               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <button type="button" 
                onclick="removeIpAddress(this)"
                class="bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
            削除
        </button>
    `;
    container.appendChild(newRow);
}

function removeIpAddress(button) {
    const container = document.getElementById('ip-addresses');
    if (container.children.length > 1) {
        button.parentElement.remove();
    }
}
</script>
@endpush