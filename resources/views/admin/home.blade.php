<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理画面ホーム - SJT-CP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- ナビゲーションバー -->
    <nav class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- ロゴ・タイトル部分 -->
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h1 class="text-xl font-bold text-gray-900 hidden sm:block">SJT-CP</h1>
                        <h1 class="text-lg font-bold text-gray-900 sm:hidden">SJT</h1>
                    </div>
                </div>

                <!-- ユーザー情報・ログアウト -->
                <div class="flex items-center space-x-4">
                    <!-- ユーザー情報 -->
                    <div class="hidden md:flex flex-col text-right">
                        <span class="text-sm font-semibold text-gray-700">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-gray-500 capitalize">
                            @if(auth()->user()->role === 'admin')
                                管理者
                            @elseif(auth()->user()->role === 'competition_committee')
                                競技委員
                            @else
                                補佐員
                            @endif
                        </span>
                    </div>

                    <!-- モバイル用ユーザー情報 -->
                    <div class="md:hidden">
                        <span class="text-sm font-semibold text-gray-700">{{ auth()->user()->name }}</span>
                    </div>

                    <!-- ログアウトボタン -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span class="hidden sm:inline">ログアウト</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- メインコンテンツ -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- ヘッダー部分 -->
        <div class="px-4 py-6 sm:px-0">
            <div class="border-4 border-dashed border-gray-200 rounded-lg p-8 text-center bg-white">
                <div class="mx-auto h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v3H8V5z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">管理画面ホーム</h2>
                <p class="text-lg text-gray-600 mb-6">SJT-CP 競技大会管理システムへようこそ</p>

                <!-- ステータス表示 -->
                <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                    <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    システム稼働中
                </div>
            </div>
        </div>

        <!-- 機能カード -->
        <div class="px-4 sm:px-0">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- スケジュール管理 -->
                <a href="{{ route('admin.competitions.index') }}" class="block bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">スケジュール管理</h3>
                                <p class="text-sm text-gray-600">競技大会のスケジュールを管理</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-blue-600 text-sm font-medium">
                                    開く →
                                </span>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- 選手情報管理 -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-all duration-300">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">選手情報管理</h3>
                                <p class="text-sm text-gray-600">選手の情報を管理</p>
                            </div>
                        </div>
                        
                        <!-- サブメニュー -->
                        <div class="space-y-2">
                            <a href="{{ route('admin.players.index') }}" 
                               class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-800 rounded-md transition-colors duration-200">
                                選手一覧
                            </a>
                            <a href="{{ route('admin.competition-players.index') }}" 
                               class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-800 rounded-md transition-colors duration-200">
                                大会選手管理
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 端末管理 -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-all duration-300">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">端末管理</h3>
                                <p class="text-sm text-gray-600">端末情報と大会割り当てを管理</p>
                            </div>
                        </div>
                        
                        <!-- サブメニュー -->
                        <div class="space-y-2">
                            <a href="{{ route('admin.devices.index') }}" 
                               class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-800 rounded-md transition-colors duration-200">
                                端末一覧
                            </a>
                            <a href="{{ route('admin.competition-devices.index') }}" 
                               class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-800 rounded-md transition-colors duration-200">
                                競技端末割り当て
                            </a>
                        </div>
                    </div>
                </div>

                <!-- システム管理 -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl opacity-75 cursor-not-allowed">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">システム管理</h3>
                                <p class="text-sm text-gray-600">ユーザーやシステム設定を管理</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    準備中
                                </span>
                                <span class="text-purple-600 text-sm font-medium opacity-50">
                                    開く →
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- システム情報 -->
        <div class="px-4 sm:px-0 mt-8">
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">システム情報</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">バージョン</dt>
                            <dd class="mt-1 text-sm text-gray-900">v1.0.0 (開発版)</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">最終更新</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ now()->format('Y年m月d日 H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">接続ユーザー</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ auth()->user()->name }} ({{ auth()->user()->role }})</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">機能状態</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    基本機能 稼働中
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
