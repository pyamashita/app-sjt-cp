<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>端末割り当て詳細 - SJT-CP</title>
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
                    <a href="{{ route('admin.competition-devices.index') }}" class="ml-4 text-gray-600 hover:text-gray-900">競技端末割り当て</a>
                    <span class="ml-2 text-gray-400">></span>
                    <span class="ml-2 text-gray-600 font-medium">詳細</span>
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
                        <h2 class="text-3xl font-bold text-gray-900">端末割り当て詳細</h2>
                        <p class="text-gray-600 mt-2">選手番号 {{ $competitionDevice->player_number }} の端末割り当て詳細です。</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.competition-devices.edit', $competitionDevice) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            編集
                        </a>
                    </div>
                </div>
            </div>

            <!-- 大会情報 -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden mb-8">
                <div class="px-6 py-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">大会情報</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">大会名</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $competitionDevice->competition->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">開催期間</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $competitionDevice->competition->period }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">開催場所</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $competitionDevice->competition->venue }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">競技主査</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $competitionDevice->competition->chief_judge ?? '未設定' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- 割り当て情報 -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden mb-8">
                <div class="px-6 py-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">割り当て情報</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">選手番号</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ $competitionDevice->player_number }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">割り当て日</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $competitionDevice->created_at->format('Y年m月d日 H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">最終更新</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $competitionDevice->updated_at->format('Y年m月d日 H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- 端末情報 -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden mb-8">
                <div class="px-6 py-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">端末情報</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">端末名</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $competitionDevice->device->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">端末種別</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($competitionDevice->device->type === 'PC') bg-blue-100 text-blue-800
                                    @elseif($competitionDevice->device->type === 'スマートフォン') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $competitionDevice->device->type }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">利用者</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $competitionDevice->device->user_type }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">端末登録日</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $competitionDevice->device->created_at->format('Y年m月d日') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">IPアドレス</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $competitionDevice->device->ip_address ?? '未設定' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">MACアドレス</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $competitionDevice->device->mac_address ?? '未設定' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- 操作ボタン -->
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.competition-devices.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    一覧に戻る
                </a>
                
                <div class="flex space-x-3">
                    <a href="{{ route('admin.devices.show', $competitionDevice->device) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        端末詳細
                    </a>
                    
                    <form method="POST" action="{{ route('admin.competition-devices.destroy', $competitionDevice) }}" class="inline" 
                          onsubmit="return confirm('この割り当てを解除しますか？')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            割り当て解除
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>