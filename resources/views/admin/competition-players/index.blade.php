<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>大会選手管理 - SkillJapan Tools</title>
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
                    <span class="ml-4 text-gray-600 font-medium">大会選手管理</span>
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
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">大会選手管理</h2>
                    <p class="text-gray-600 mt-1">大会への選手割り当てを管理します</p>
                </div>
            </div>

            <!-- 成功メッセージ -->
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- エラーメッセージ -->
            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 大会選択 -->
            <div class="bg-white shadow-lg rounded-xl p-6 mb-6">
                <form method="GET" action="{{ route('admin.competition-players.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label for="competition_id" class="block text-sm font-medium text-gray-700 mb-2">大会を選択</label>
                            <select id="competition_id" 
                                    name="competition_id" 
                                    onchange="this.form.submit()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">大会を選択してください</option>
                                @foreach($competitions as $competition)
                                    <option value="{{ $competition->id }}" {{ request('competition_id') == $competition->id ? 'selected' : '' }}>
                                        {{ $competition->name }} ({{ $competition->period }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if($selectedCompetition)
                            <div class="flex items-end">
                                <a href="{{ route('admin.competition-players.create', ['competition_id' => $selectedCompetition->id]) }}" 
                                   class="w-full px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-200 text-center">
                                    選手を追加
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            @if($selectedCompetition)
                <!-- 検索・操作 -->
                <div class="bg-white shadow-lg rounded-xl p-6 mb-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
                        <div class="flex-1 max-w-md">
                            <form method="GET" action="{{ route('admin.competition-players.index') }}">
                                <input type="hidden" name="competition_id" value="{{ $selectedCompetition->id }}">
                                <div class="flex">
                                    <input type="text" 
                                           name="search" 
                                           value="{{ request('search') }}"
                                           placeholder="選手名、都道府県、所属、選手番号で検索"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button type="submit" 
                                            class="px-4 py-2 bg-blue-600 text-white rounded-r-md hover:bg-blue-700 transition duration-200">
                                        検索
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="openGenerateModal()" 
                                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition duration-200">
                                選手番号生成
                            </button>
                            <button onclick="openImportModal()" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200">
                                CSVインポート
                            </button>
                            <a href="{{ route('admin.competition-players.export', array_merge(request()->all(), ['competition_id' => $selectedCompetition->id])) }}" 
                               class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition duration-200">
                                CSVエクスポート
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 選手一覧 -->
                <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $selectedCompetition->name }} - 参加選手一覧
                            <span class="text-sm text-gray-500 ml-2">({{ $competitionPlayers->total() }}人)</span>
                        </h3>
                    </div>
                    
                    @if($competitionPlayers->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">選手番号</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">選手名</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">都道府県</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">所属</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">性別</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($competitionPlayers as $competitionPlayer)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $competitionPlayer->player_number }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $competitionPlayer->player->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $competitionPlayer->player->prefecture }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $competitionPlayer->player->affiliation ?? '未設定' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $competitionPlayer->player->gender_label }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                <a href="{{ route('admin.competition-players.show', $competitionPlayer) }}" 
                                                   class="text-blue-600 hover:text-blue-900">詳細</a>
                                                <a href="{{ route('admin.competition-players.edit', $competitionPlayer) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900">編集</a>
                                                <form action="{{ route('admin.competition-players.destroy', $competitionPlayer) }}" 
                                                      method="POST" class="inline" 
                                                      onsubmit="return confirm('この選手の割り当てを解除しますか？')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">解除</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- ページネーション -->
                        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                            {{ $competitionPlayers->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="mx-auto h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">参加選手がいません</h3>
                            <p class="text-gray-600 mb-6">この大会にはまだ選手が割り当てられていません。</p>
                            <a href="{{ route('admin.competition-players.create', ['competition_id' => $selectedCompetition->id]) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-200">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                選手を追加
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </main>

    @if($selectedCompetition)
        <!-- CSVインポートモーダル -->
        <div id="importModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">CSVインポート</h3>
                        <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <form action="{{ route('admin.competition-players.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="competition_id" value="{{ $selectedCompetition->id }}">
                        <div class="mb-4">
                            <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">CSVファイル</label>
                            <input type="file" 
                                   id="csv_file" 
                                   name="csv_file" 
                                   accept=".csv,.txt" 
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">形式: 選手番号,選手名,都道府県,所属,性別</p>
                        </div>
                        <div class="mb-4">
                            <label for="import_mode" class="block text-sm font-medium text-gray-700 mb-2">インポートモード</label>
                            <select id="import_mode" 
                                    name="import_mode" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="append">追加（既存データを保持）</option>
                                <option value="replace">置換（既存データを削除）</option>
                            </select>
                        </div>
                        <div class="flex space-x-3">
                            <button type="submit" 
                                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                                インポート
                            </button>
                            <button type="button" 
                                    onclick="closeImportModal()" 
                                    class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200">
                                キャンセル
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 選手番号生成モーダル -->
        <div id="generateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">選手番号生成</h3>
                        <button onclick="closeGenerateModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <form action="{{ route('admin.competition-players.generate-player-numbers') }}" method="POST">
                        @csrf
                        <input type="hidden" name="competition_id" value="{{ $selectedCompetition->id }}">
                        <div class="mb-4">
                            <label for="start_number" class="block text-sm font-medium text-gray-700 mb-2">開始番号</label>
                            <input type="number" 
                                   id="start_number" 
                                   name="start_number" 
                                   value="1"
                                   min="1"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="mb-4">
                            <label for="format" class="block text-sm font-medium text-gray-700 mb-2">番号形式</label>
                            <select id="format" 
                                    name="format" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="number">数字のみ (1, 2, 3...)</option>
                                <option value="prefixed">接頭辞付き (No.001, No.002...)</option>
                            </select>
                        </div>
                        <div class="flex space-x-3">
                            <button type="submit" 
                                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                                生成
                            </button>
                            <button type="button" 
                                    onclick="closeGenerateModal()" 
                                    class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200">
                                キャンセル
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
        }

        function openGenerateModal() {
            document.getElementById('generateModal').classList.remove('hidden');
        }

        function closeGenerateModal() {
            document.getElementById('generateModal').classList.add('hidden');
        }

        // モーダル外クリックで閉じる
        document.getElementById('importModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeImportModal();
            }
        });

        document.getElementById('generateModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeGenerateModal();
            }
        });
    </script>
</body>
</html>