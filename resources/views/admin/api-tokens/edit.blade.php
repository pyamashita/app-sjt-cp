@extends('layouts.admin')

@section('title', $apiToken->name . ' - APIトークン編集 - SJT-CP')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $apiToken->name }} - 編集</h1>
        <p class="mt-2 text-sm text-gray-600">APIトークン情報を編集します</p>
    </div>

    <form method="POST" action="{{ route('admin.api-tokens.update', $apiToken) }}">
        @csrf
        @method('PUT')
        
        <x-form-card title="基本情報">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form-field
                    name="name"
                    label="トークン名"
                    type="text"
                    :value="old('name', $apiToken->name)"
                    placeholder="トークン名を入力"
                    required
                />
                
                <x-form-field
                    name="expires_at"
                    label="有効期限"
                    type="datetime-local"
                    :value="old('expires_at', $apiToken->expires_at ? $apiToken->expires_at->format('Y-m-d\TH:i') : '')"
                    help-text="空の場合は無期限"
                />
                
                <x-form-field
                    name="description"
                    label="説明"
                    type="textarea"
                    :value="old('description', $apiToken->description)"
                    placeholder="このトークンの用途を説明"
                    col-span="2"
                />
            </div>
        </x-form-card>

        <x-form-card title="権限設定">
            <div class="space-y-4">
                <p class="text-sm text-gray-600">このトークンに付与する権限を選択してください</p>
                
                @foreach(App\Models\ApiToken::getPermissions() as $key => $label)
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="permissions[]" 
                               id="permission_{{ $key }}"
                               value="{{ $key }}"
                               {{ in_array($key, old('permissions', $apiToken->permissions ?? [])) ? 'checked' : '' }}
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                        <label for="permission_{{ $key }}" class="ml-2 block text-sm text-gray-700">
                            {{ $label }}
                        </label>
                    </div>
                @endforeach
            </div>
        </x-form-card>

        <x-form-card title="アクセス制御">
            <div class="space-y-4">
                <p class="text-sm text-gray-600">このトークンからのアクセスを許可するIPアドレスを指定できます</p>
                
                <div id="ip-addresses">
                    @forelse(old('allowed_ips', $apiToken->allowed_ips ?? ['']) as $index => $ip)
                        <div class="flex items-center space-x-2 ip-address-row">
                            <input type="text" 
                                   name="allowed_ips[]" 
                                   value="{{ $ip }}"
                                   placeholder="192.168.1.100"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <button type="button" 
                                    onclick="removeIpAddress(this)"
                                    class="bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                削除
                            </button>
                        </div>
                    @empty
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
                    @endforelse
                </div>
                
                <button type="button" 
                        onclick="addIpAddress()"
                        class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    IPアドレスを追加
                </button>
            </div>
        </x-form-card>

        <x-form-card title="その他の設定">
            <div class="flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       id="is_active"
                       value="1"
                       {{ old('is_active', $apiToken->is_active) ? 'checked' : '' }}
                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">
                    有効にする
                </label>
            </div>
        </x-form-card>

        <div class="mt-6 flex items-center justify-end space-x-3">
            <a href="{{ route('admin.api-tokens.show', $apiToken) }}" 
               class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                キャンセル
            </a>
            <button type="submit" 
                    class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                更新
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