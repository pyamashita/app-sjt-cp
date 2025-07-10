<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規日程追加 - {{ $competition->name }} - SJT-CP</title>
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
                    <a href="{{ route('admin.competitions.competition-days.index', $competition) }}" class="ml-4 text-gray-600 hover:text-gray-900">{{ $competition->name }}</a>
                    <span class="ml-2 text-gray-400">></span>
                    <span class="ml-2 text-gray-600 font-medium">新規日程追加</span>
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
                <h2 class="text-3xl font-bold text-gray-900">新規日程追加</h2>
                <p class="text-gray-600 mt-2">{{ $competition->name }} の競技日程を追加してください。</p>
                <p class="text-sm text-gray-500 mt-1">大会期間: {{ $competition->period }}</p>
            </div>

            <!-- フォーム -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <div class="px-6 py-8">
                    <form action="{{ route('admin.competitions.competition-days.store', $competition) }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- 実施日名称 -->
                        <div>
                            <label for="day_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                実施日名称 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="day_name"
                                name="day_name"
                                value="{{ old('day_name') }}"
                                required
                                placeholder="例：1日目 -実施準備、競技A-"
                                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
                            >
                            @error('day_name')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 実施日 -->
                        <div>
                            <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">
                                実施日 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                id="date"
                                name="date"
                                value="{{ old('date') }}"
                                min="{{ $competition->start_date->format('Y-m-d') }}"
                                max="{{ $competition->end_date->format('Y-m-d') }}"
                                required
                                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
                            >
                            @error('date')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">
                                大会期間内の日付を選択してください（{{ $competition->start_date->format('Y-m-d') }} 〜 {{ $competition->end_date->format('Y-m-d') }}）
                            </p>
                        </div>

                        <!-- フォームボタン -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <a href="{{ route('admin.competitions.competition-days.index', $competition) }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                                キャンセル
                            </a>
                            <button
                                type="submit"
                                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200"
                            >
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                日程を追加
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
