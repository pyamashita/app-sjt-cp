@extends('layouts.admin')

@section('title', '外部接続設定 - SJT-CP')

@php
    $pageTitle = '外部接続設定';
    $pageDescription = '外部システムとの接続設定を管理します';
@endphp

@section('content')
    <!-- 接続設定一覧 -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($connections->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                サービス名
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                状態
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                設定概要
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                最終更新
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                操作
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($connections as $connection)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $connection->name }}
                                        </div>
                                        @if($connection->description)
                                            <div class="text-sm text-gray-500">
                                                {{ $connection->description }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $connection->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $connection->is_active ? '有効' : '無効' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        @if($connection->service_type === 'websocket_message')
                                            <div>サーバー: {{ ($connection->config['use_localhost'] ?? true) ? 'localhost' : 'カスタムアドレス' }}</div>
                                            @if(!($connection->config['use_localhost'] ?? true) && !empty($connection->config['server_address']))
                                                <div>アドレス: {{ $connection->config['server_address'] }}</div>
                                            @endif
                                            <div>プロトコル: {{ $connection->config['protocol'] ?? 'ws' }}</div>
                                            <div>デフォルトポート: {{ $connection->config['default_port'] ?? '8080' }}</div>
                                            <div>タイムアウト: {{ $connection->config['timeout'] ?? '10' }}秒</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>{{ $connection->updated_at->format('Y/m/d H:i') }}</div>
                                    @if($connection->updater)
                                        <div class="text-gray-500">{{ $connection->updater->name }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.external-connections.edit', $connection) }}"
                                           class="text-blue-600 hover:text-blue-900">設定</a>

                                        @if($connection->service_type === 'websocket_message')
                                            <button type="button"
                                                    onclick="testConnection({{ $connection->id }}, this)"
                                                    class="text-green-600 hover:text-green-900">
                                                テスト
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-gray-500">外部接続設定が見つかりません。</p>
            </div>
        @endif
    </div>

    <script>
        function testConnection(connectionId, buttonElement) {
            // テストボタンを無効化
            buttonElement.disabled = true;
            buttonElement.textContent = 'テスト中...';

            fetch(`/admin/external-connections/${connectionId}/test`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('接続テストエラー:', error);
                alert('❌ 接続テストでエラーが発生しました: ' + error.message);
            })
            .finally(() => {
                // テストボタンを有効化
                buttonElement.disabled = false;
                buttonElement.textContent = 'テスト';
            });
        }
    </script>
@endsection
