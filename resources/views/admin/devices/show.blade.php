<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>端末詳細 - SJT-CP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- ナビゲーションバー -->
    <nav class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('admin.home') }}" class="flex items-center">
                        <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h1 class="text-xl font-bold text-gray-900">SJT-CP</h1>
                    </a>
                    <span class="ml-4 text-gray-400">|</span>
                    <a href="{{ route('admin.devices.index') }}" class="ml-4 text-gray-600 hover:text-gray-900">端末管理</a>
                    <span class="ml-2 text-gray-400">></span>
                    <span class="ml-2 text-gray-600 font-medium">{{ $device->name }}</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-700">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">ログアウト</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- メインコンテンツ -->
    <main class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <!-- ヘッダー -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900">端末詳細</h2>
                        <p class="text-gray-600 mt-2">{{ $device->name }} の詳細情報です。</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.devices.edit', $device) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            編集
                        </a>
                    </div>
                </div>
            </div>

            <!-- 端末情報 -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden mb-8">
                <div class="px-6 py-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">基本情報</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">端末名</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $device->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">端末種別</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($device->type === 'PC') bg-blue-100 text-blue-800
                                    @elseif($device->type === 'スマートフォン') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $device->type }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">利用者</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($device->user_type === '選手') bg-purple-100 text-purple-800
                                    @elseif($device->user_type === '競技関係者') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $device->user_type }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">登録日</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $device->created_at->format('Y年m月d日 H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">IPアドレス</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $device->ip_address ?? '未設定' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">MACアドレス</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $device->mac_address ?? '未設定' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- 大会割り当て履歴 -->
            @if($device->user_type === '選手' && $device->competitions->count() > 0)
                <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                    <div class="px-6 py-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">大会割り当て履歴</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">大会名</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">選手番号</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">開催期間</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">開催場所</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($device->competitions as $competition)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $competition->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $competition->pivot->player_number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $competition->period }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $competition->venue }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @elseif($device->user_type === '選手')
                <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                    <div class="px-6 py-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">大会割り当てなし</h3>
                        <p class="mt-1 text-sm text-gray-500">この端末はまだ大会に割り当てられていません。</p>
                    </div>
                </div>
            @endif

            <!-- 戻るボタン -->
            <div class="mt-8">
                <a href="{{ route('admin.devices.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    端末一覧に戻る
                </a>
            </div>
        </div>
    </main>
</body>
</html>