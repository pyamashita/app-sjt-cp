<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $guidePage->title }} - {{ $guidePage->competition->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/guide.css') }}">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- ヘッダー -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $guidePage->title }}</h1>
                <p class="text-lg text-gray-600">{{ $guidePage->competition->name }}</p>
                <div class="mt-4 space-y-2">
                    <div class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                        プレビューモード
                    </div>
                    @if($hasPlayerManagedCollections)
                        <div class="mt-4">
                            <form method="GET" action="{{ route('admin.guide-pages.preview', $guidePage) }}" class="flex items-center justify-center space-x-2">
                                <label for="emulate_player_id" class="text-sm font-medium text-gray-700">選手エミュレート:</label>
                                <select name="emulate_player_id" id="emulate_player_id" 
                                        class="px-3 py-1 border border-gray-300 rounded text-sm"
                                        onchange="this.form.submit()">
                                    <option value="">選手を選択してください</option>
                                    @foreach($availablePlayers as $player)
                                        <option value="{{ $player->id }}" {{ request('emulate_player_id') == $player->id ? 'selected' : '' }}>
                                            {{ $player->player_number }} - {{ $player->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($emulatePlayer)
                                    <div class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                        {{ $emulatePlayer->player_number }} - {{ $emulatePlayer->name }} として表示中
                                    </div>
                                @endif
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- コンテンツ -->
        @foreach($guidePage->sections as $section)
            <div class="contentBox">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $section->title }}</h2>
                
                @if($section->groups->count() > 0)
                    <div class="gridBox">
                        @foreach($section->groups as $group)
                            <div class="gridChild">
                                <h3>{{ $group->title }}</h3>
                                <div class="content">
                                    @if($group->items->count() > 0)
                                        <ul>
                                            @foreach($group->items as $item)
                                                <li class="mb-2">
                                                    @if($item->type === 'text')
                                                        <div class="text-item">
                                                            <div class="font-medium text-gray-900 mb-1">{{ $item->title }}</div>
                                                            <div class="text-gray-700 text-sm bg-gray-50 p-3 rounded border-l-4 border-blue-200">
                                                                {{ $item->text_content }}
                                                                @if($item->show_copy_button)
                                                                    <button onclick="copyToClipboard('{{ addslashes($item->text_content) }}')" 
                                                                            class="ml-2 px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded hover:bg-blue-200">
                                                                        コピー
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <a href="{{ $item->getDisplayUrl($emulatePlayer?->id) }}" 
                                                           target="{{ $item->getTarget() }}"
                                                           class="flex items-center space-x-2 text-blue-600 hover:text-blue-800">
                                                            <span>{{ $item->title }}</span>
                                                            @if($item->type === 'collection')
                                                                <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">コレクション</span>
                                                                @if($item->collection && $item->collection->is_player_managed && !$emulatePlayer)
                                                                    <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">選手選択が必要</span>
                                                                @endif
                                                            @endif
                                                            @if($item->open_in_new_tab)
                                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
                                                                    <path d="M5 5a2 2 0 00-2 2v6a2 2 0 002 2h6a2 2 0 002-2v-2a1 1 0 10-2 0v2H5V7h2a1 1 0 000-2H5z"></path>
                                                                </svg>
                                                            @endif
                                                        </a>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-gray-500 text-sm">アイテムが登録されていません。</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">グループが登録されていません。</p>
                @endif
            </div>
        @endforeach

        @if($guidePage->sections->count() === 0)
            <div class="contentBox">
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">コンテンツがありません</h3>
                    <p class="text-gray-500">セクション、グループ、アイテムを追加してガイドページを作成してください。</p>
                </div>
            </div>
        @endif

        <!-- フッター -->
        <div class="bg-white rounded-lg shadow-sm p-4 mt-8">
            <div class="flex justify-between items-center text-sm text-gray-500">
                <div>
                    最終更新: {{ $guidePage->updated_at->format('Y年m月d日 H:i') }}
                </div>
                <div>
                    ステータス: 
                    @if($guidePage->is_active)
                        <span class="text-green-600 font-medium">有効</span>
                    @else
                        <span class="text-gray-600 font-medium">無効</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // 成功時の表示
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = 'コピー済み';
                button.classList.remove('bg-blue-100', 'text-blue-700', 'hover:bg-blue-200');
                button.classList.add('bg-green-100', 'text-green-700');
                
                setTimeout(function() {
                    button.textContent = originalText;
                    button.classList.remove('bg-green-100', 'text-green-700');
                    button.classList.add('bg-blue-100', 'text-blue-700', 'hover:bg-blue-200');
                }, 2000);
            }).catch(function(err) {
                console.error('コピーに失敗しました: ', err);
                alert('コピーに失敗しました');
            });
        }
    </script>
</body>
</html>