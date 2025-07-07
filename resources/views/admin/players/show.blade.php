<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>選手詳細 - SkillJapan Tools</title>
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
                    <span class="ml-4 text-gray-600 font-medium">選手詳細</span>
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
                    <h2 class="text-2xl font-bold text-gray-900">選手詳細</h2>
                    <p class="text-gray-600 mt-1">{{ $player->name }} の詳細情報</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.players.edit', $player) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        編集
                    </a>
                    <a href="{{ route('admin.players.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-400 transition duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        戻る
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- 基本情報 -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow-lg rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">基本情報</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">選手名</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ $player->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">都道府県</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ $player->prefecture }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">所属</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ $player->affiliation ?? '未設定' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">性別</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ $player->gender_label }}</dd>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">登録日</dt>
                                    <dd class="text-sm text-gray-900 mt-1">{{ $player->created_at->format('Y年m月d日 H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">最終更新</dt>
                                    <dd class="text-sm text-gray-900 mt-1">{{ $player->updated_at->format('Y年m月d日 H:i') }}</dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 統計情報 -->
                <div>
                    <div class="bg-white shadow-lg rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">統計情報</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">参加大会数</span>
                                <span class="text-lg font-semibold text-blue-600">{{ $player->competitionPlayers->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 参加大会一覧 -->
            <div class="mt-6">
                <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">参加大会一覧</h3>
                    </div>
                    
                    @if($player->competitionPlayers->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">大会名</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">開催期間</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">選手番号</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">開催場所</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($player->competitionPlayers as $competitionPlayer)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $competitionPlayer->competition->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $competitionPlayer->competition->period }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $competitionPlayer->player_number }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $competitionPlayer->competition->venue }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('admin.competitions.show', $competitionPlayer->competition) }}" 
                                                   class="text-blue-600 hover:text-blue-900">大会詳細</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="mx-auto h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">参加大会がありません</h3>
                            <p class="text-gray-600">この選手はまだ大会に参加していません。</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</body>
</html>