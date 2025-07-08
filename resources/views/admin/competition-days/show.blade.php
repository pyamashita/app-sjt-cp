<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $competitionDay->day_name }} - スケジュール管理 - SJT-CP</title>
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
                    <span class="ml-2 text-gray-600 font-medium">{{ $competitionDay->day_name }}</span>
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
                        <h2 class="text-2xl font-bold text-gray-900">{{ $competitionDay->day_name }}</h2>
                        <p class="text-gray-600 mt-1">{{ $competitionDay->formatted_date }}（{{ $competitionDay->day_of_week }}）</p>
                        <div class="mt-2 flex items-center space-x-4">
                            <a href="{{ route('admin.competitions.competition-days.index', $competition) }}"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                ← {{ $competition->name }} に戻る
                            </a>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.competitions.competition-days.edit', [$competition, $competitionDay]) }}"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            日程編集
                        </a>
                    </div>
                </div>
            </div>

            <!-- 成功メッセージ -->
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- スケジュール管理 -->
            <div id="app">
                <schedule-manager
                    :competition-day-id="{{ $competitionDay->id }}"
                    :initial-schedules="{{ json_encode($competitionDay->competitionSchedules->map(function($schedule) {
                        return [
                            'id' => $schedule->id,
                            'start_time' => $schedule->start_time->format('H:i'),
                            'content' => $schedule->content,
                            'notes' => $schedule->notes,
                            'count_up' => $schedule->count_up,
                            'auto_advance' => $schedule->auto_advance,
                            'order' => $schedule->sort_order
                        ];
                    })->sortBy('order')->values()) }}"
                    csrf-token="{{ csrf_token() }}"
                ></schedule-manager>
            </div>
        </div>
    </main>
</body>
</html>
