<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>選手割り当て詳細 - SJT-CP</title>
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
                    <span class="ml-4 text-gray-600 font-medium">選手情報管理 / 大会選手割当</span>
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
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">選手割り当て詳細</h2>
                    <p class="text-gray-600 mt-1">{{ $competitionPlayer->player->name }} の大会参加情報</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.competition-players.edit', $competitionPlayer) }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        編集
                    </a>
                    <a href="{{ route('admin.competition-players.index', ['competition_id' => $competitionPlayer->competition_id]) }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-400 transition duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        戻る
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 大会情報 -->
                <div class="bg-white shadow-lg rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">大会情報</h3>
                    <div class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">大会名</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $competitionPlayer->competition->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">開催期間</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $competitionPlayer->competition->period }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">開催場所</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $competitionPlayer->competition->venue }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">競技主査</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $competitionPlayer->competition->chief_judge ?? '未設定' }}</dd>
                        </div>
                        @if($competitionPlayer->competition->committee_members)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">競技委員</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ $competitionPlayer->competition->committee_members_string }}</dd>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 選手情報 -->
                <div class="bg-white shadow-lg rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">選手情報</h3>
                    <div class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">選手名</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $competitionPlayer->player->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">都道府県</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $competitionPlayer->player->prefecture }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">所属</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $competitionPlayer->player->affiliation ?? '未設定' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">性別</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $competitionPlayer->player->gender_label }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">選手番号</dt>
                            <dd class="text-sm text-gray-900 mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $competitionPlayer->player_number }}
                                </span>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 参加履歴 -->
            <div class="mt-6">
                <div class="bg-white shadow-lg rounded-xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">割り当て情報</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">割り当て日</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $competitionPlayer->created_at->format('Y年m月d日 H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">最終更新</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $competitionPlayer->updated_at->format('Y年m月d日 H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ステータス</dt>
                            <dd class="text-sm text-gray-900 mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    参加中
                                </span>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- アクション -->
            <div class="mt-6">
                <div class="bg-white shadow-lg rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">アクション</h3>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.players.show', $competitionPlayer->player) }}"
                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            選手詳細を見る
                        </a>

                        <a href="{{ route('admin.competitions.show', $competitionPlayer->competition) }}"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            大会詳細を見る
                        </a>

                        <form action="{{ route('admin.competition-players.destroy', $competitionPlayer) }}"
                              method="POST" class="inline"
                              onsubmit="return confirm('この選手の大会への割り当てを解除しますか？')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition duration-200">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                割り当て解除
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
