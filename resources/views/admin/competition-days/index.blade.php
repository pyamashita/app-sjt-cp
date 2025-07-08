<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $competition->name }} - 競技日程管理 - SJT-CP</title>
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
                    <a href="{{ route('admin.competitions.index') }}" class="ml-4 text-gray-600 hover:text-gray-900">大会実施管理</a>
                    <span class="ml-2 text-gray-400">></span>
                    <span class="ml-2 text-gray-600 font-medium">{{ $competition->name }}</span>
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
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <!-- ヘッダー -->
            <div class="mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $competition->name }}</h2>
                        <p class="text-gray-600 mt-1">{{ $competition->period }} | {{ $competition->venue }}</p>
                        <div class="mt-2 flex items-center space-x-4">
                            <a href="{{ route('admin.competitions.index') }}"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                ← 大会一覧に戻る
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('admin.competitions.competition-days.create', $competition) }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        新規日程追加
                    </a>
                </div>
            </div>

            <!-- 成功メッセージ -->
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- 競技日程一覧 -->
            @if($competitionDays->count() > 0)
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($competitionDays as $day)
                        <div class="bg-white shadow-lg rounded-xl overflow-hidden hover:shadow-xl transition-shadow duration-300">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $day->day_name }}</h3>
                                        <p class="text-sm text-gray-600">{{ $day->formatted_date }}（{{ $day->day_of_week }}）</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $day->competitionSchedules->count() }}件
                                        </span>
                                    </div>
                                </div>

                                <!-- スケジュール概要 -->
                                @if($day->competitionSchedules->count() > 0)
                                    <div class="space-y-2 mb-4">
                                        @foreach($day->competitionSchedules->take(3) as $schedule)
                                            <div class="flex items-center text-sm">
                                                <span class="text-gray-500 font-mono">{{ $schedule->formatted_start_time }}</span>
                                                <span class="ml-2 text-gray-700 truncate">{{ $schedule->content }}</span>
                                            </div>
                                        @endforeach
                                        @if($day->competitionSchedules->count() > 3)
                                            <div class="text-xs text-gray-500">
                                                他 {{ $day->competitionSchedules->count() - 3 }}件...
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <div class="text-gray-400 text-sm">スケジュールが登録されていません</div>
                                    </div>
                                @endif

                                <!-- アクション -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.competitions.competition-days.show', [$competition, $day]) }}"
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            詳細・編集
                                        </a>
                                        <a href="{{ route('admin.competitions.competition-days.edit', [$competition, $day]) }}"
                                           class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                            日程編集
                                        </a>
                                    </div>
                                    <form action="{{ route('admin.competitions.competition-days.destroy', [$competition, $day]) }}"
                                          method="POST" class="inline"
                                          onsubmit="return confirm('この日程とすべてのスケジュールを削除しますか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            削除
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                    <div class="text-center py-12">
                        <div class="mx-auto h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">競技日程が登録されていません</h3>
                        <p class="text-gray-600 mb-6">この大会の競技日程を追加してください。</p>
                        <a href="{{ route('admin.competitions.competition-days.create', $competition) }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            新規日程追加
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </main>
</body>
</html>
