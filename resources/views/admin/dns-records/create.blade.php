@extends('layouts.admin')

@section('title', 'DNSレコード追加 - SJT-CP')

@php
    $pageTitle = 'DNSレコード追加';
    $pageDescription = $server->hostname . ' にDNSレコードを追加します';
    $breadcrumbs = [
        ['label' => 'サーバ管理', 'url' => route('admin.servers.index')],
        ['label' => $server->hostname, 'url' => route('admin.servers.show', $server)],
        ['label' => 'DNSレコード追加', 'url' => '#']
    ];
@endphp

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">DNSレコード情報入力</h3>
                <p class="text-sm text-gray-600 mt-1">サーバ: {{ $server->hostname }} ({{ $server->type_display_name }})</p>
            </div>
            
            <form method="POST" action="{{ route('admin.dns-records.store') }}" class="px-6 py-6 space-y-6">
                @csrf
                <input type="hidden" name="server_id" value="{{ $server->id }}">
                
                <!-- レコード名 -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        レコード名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           placeholder="例: www.example.com または @"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- レコードタイプ -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        レコードタイプ <span class="text-red-500">*</span>
                    </label>
                    <select id="type" name="type" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">選択してください</option>
                        @foreach(\App\Models\DnsRecord::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- レコード値 -->
                <div>
                    <label for="value" class="block text-sm font-medium text-gray-700 mb-2">
                        レコード値 <span class="text-red-500">*</span>
                    </label>
                    <textarea id="value" name="value" rows="3" required
                              placeholder="レコードタイプに応じた値を入力してください"
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('value') }}</textarea>
                    <div id="valueHelp" class="mt-1 text-sm text-gray-500"></div>
                    @error('value')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- TTL -->
                <div>
                    <label for="ttl" class="block text-sm font-medium text-gray-700 mb-2">
                        TTL（秒） <span class="text-red-500">*</span>
                    </label>
                    <select id="ttl" name="ttl" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">選択してください</option>
                        <option value="300" {{ old('ttl') == '300' ? 'selected' : '' }}>300 (5分)</option>
                        <option value="1800" {{ old('ttl') == '1800' ? 'selected' : '' }}>1800 (30分)</option>
                        <option value="3600" {{ old('ttl', '3600') == '3600' ? 'selected' : '' }}>3600 (1時間) - 推奨</option>
                        <option value="7200" {{ old('ttl') == '7200' ? 'selected' : '' }}>7200 (2時間)</option>
                        <option value="14400" {{ old('ttl') == '14400' ? 'selected' : '' }}>14400 (4時間)</option>
                        <option value="43200" {{ old('ttl') == '43200' ? 'selected' : '' }}>43200 (12時間)</option>
                        <option value="86400" {{ old('ttl') == '86400' ? 'selected' : '' }}>86400 (24時間)</option>
                        <option value="custom" {{ old('ttl') && !in_array(old('ttl'), ['300', '1800', '3600', '7200', '14400', '43200', '86400']) ? 'selected' : '' }}>カスタム</option>
                    </select>
                    <div id="customTtlField" class="mt-2" style="display: none;">
                        <input type="number" id="custom_ttl" name="custom_ttl" value="{{ old('custom_ttl') }}" min="1"
                               placeholder="TTL値を秒単位で入力"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    @error('ttl')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- 優先度（MX、SRVレコード用） -->
                <div id="priorityField" style="display: none;">
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                        優先度
                    </label>
                    <input type="number" id="priority" name="priority" value="{{ old('priority') }}" min="0"
                           placeholder="例: 10"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500">MXレコードとSRVレコードの場合に必要です。数値が小さいほど優先度が高くなります。</p>
                    @error('priority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- 説明 -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        説明
                    </label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="DNSレコードの用途や特記事項を入力してください"
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- アクティブ状態 -->
                <div>
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            アクティブ（稼働中状態にする）
                        </label>
                    </div>
                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- ボタン -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.servers.show', $server) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        戻る
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        DNSレコードを追加
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const typeSelect = document.getElementById('type');
        const valueHelp = document.getElementById('valueHelp');
        const priorityField = document.getElementById('priorityField');
        const priorityInput = document.getElementById('priority');

        // レコードタイプに応じてヘルプテキストと優先度フィールドを更新
        function updateFieldsBasedOnType() {
            const selectedType = typeSelect.value;
            
            // ヘルプテキストを更新
            const helpTexts = {
                'A': 'IPv4アドレスを入力してください（例: 192.168.1.100）',
                'AAAA': 'IPv6アドレスを入力してください（例: 2001:db8::1）',
                'CNAME': '正規ドメイン名を入力してください（例: example.com）',
                'MX': 'メールサーバーのホスト名を入力してください（例: mail.example.com）',
                'TXT': 'テキスト値を入力してください（例: "v=spf1 include:_spf.google.com ~all"）',
                'PTR': '逆引き用のドメイン名を入力してください（例: example.com）',
                'SRV': 'SRVレコード値を入力してください（例: 10 5 5060 sip.example.com）',
                'NS': 'ネームサーバーのホスト名を入力してください（例: ns1.example.com）',
                'SOA': 'SOAレコード値を入力してください'
            };
            
            valueHelp.textContent = helpTexts[selectedType] || '';
            
            // 優先度フィールドの表示/非表示
            if (selectedType === 'MX' || selectedType === 'SRV') {
                priorityField.style.display = 'block';
                priorityInput.required = true;
            } else {
                priorityField.style.display = 'none';
                priorityInput.required = false;
                priorityInput.value = '';
            }
        }

        typeSelect.addEventListener('change', updateFieldsBasedOnType);

        // TTLカスタムフィールドの表示/非表示
        const ttlSelect = document.getElementById('ttl');
        const customTtlField = document.getElementById('customTtlField');
        const customTtlInput = document.getElementById('custom_ttl');

        function toggleCustomTtlField() {
            if (ttlSelect.value === 'custom') {
                customTtlField.style.display = 'block';
                customTtlInput.required = true;
            } else {
                customTtlField.style.display = 'none';
                customTtlInput.required = false;
                customTtlInput.value = '';
            }
        }

        ttlSelect.addEventListener('change', toggleCustomTtlField);
        
        // ページロード時にも実行
        updateFieldsBasedOnType();
        toggleCustomTtlField();

        // フォーム送信時にカスタム値を設定
        document.querySelector('form').addEventListener('submit', function() {
            if (ttlSelect.value === 'custom') {
                ttlSelect.value = customTtlInput.value;
            }
        });
    </script>
    @endpush
@endsection