<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スケジュール編集 - {{ $competitionDay->day_name }} - SkillJapan Tools</title>
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
                        <h1 class="text-xl font-bold text-gray-900">SkillJapan Tools</h1>
                    </a>
                    <span class="ml-4 text-gray-400">|</span>
                    <a href="{{ route('admin.competitions.competition-days.show', [$competitionDay->competition, $competitionDay]) }}" class="ml-4 text-gray-600 hover:text-gray-900">{{ $competitionDay->day_name }}</a>
                    <span class="ml-2 text-gray-400">></span>
                    <span class="ml-2 text-gray-600 font-medium">スケジュール編集</span>
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
                <h2 class="text-3xl font-bold text-gray-900">スケジュール編集</h2>
                <p class="text-gray-600 mt-2">{{ $competitionDay->day_name }}（{{ $competitionDay->formatted_date }}）のスケジュールを編集してください。</p>
            </div>

            <!-- フォーム -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <div class="px-6 py-8">
                    <form action="{{ route('admin.competition-schedules.update', [$competitionDay, $competitionSchedule]) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- 開始時刻 -->
                        <div>
                            <label for="start_time" class="block text-sm font-semibold text-gray-700 mb-2">
                                開始時刻 <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="time" 
                                id="start_time" 
                                name="start_time" 
                                value="{{ old('start_time', $competitionSchedule->start_time->format('H:i')) }}"
                                required
                                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
                            >
                            @error('start_time')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 内容 -->
                        <div>
                            <label for="content" class="block text-sm font-semibold text-gray-700 mb-2">
                                内容 <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="content" 
                                name="content" 
                                value="{{ old('content', $competitionSchedule->content) }}"
                                required
                                placeholder="例：開会式"
                                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
                            >
                            @error('content')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 備考 -->
                        <div>
                            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                備考
                            </label>
                            <textarea 
                                id="notes" 
                                name="notes" 
                                rows="3"
                                placeholder="詳細な説明や注意事項があれば入力してください"
                                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
                            >{{ old('notes', $competitionSchedule->notes) }}</textarea>
                            @error('notes')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 表示エフェクト設定 -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-4">
                                表示エフェクト設定
                            </label>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        id="count_up" 
                                        name="count_up" 
                                        value="1"
                                        {{ old('count_up', $competitionSchedule->count_up) ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition duration-200"
                                    >
                                    <label for="count_up" class="ml-3 block text-sm text-gray-700">
                                        カウントアップ表示
                                        <span class="block text-xs text-gray-500">プロジェクター表示でカウントアップタイマーを表示します</span>
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        id="auto_advance" 
                                        name="auto_advance" 
                                        value="1"
                                        {{ old('auto_advance', $competitionSchedule->auto_advance) ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition duration-200"
                                    >
                                    <label for="auto_advance" class="ml-3 block text-sm text-gray-700">
                                        自動送り
                                        <span class="block text-xs text-gray-500">プロジェクター表示で自動的に次のスケジュールに進みます</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- フォームボタン -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <a href="{{ route('admin.competitions.competition-days.show', [$competitionDay->competition, $competitionDay]) }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                                キャンセル
                            </a>
                            <button 
                                type="submit"
                                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200"
                            >
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                スケジュールを更新
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>