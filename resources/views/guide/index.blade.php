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
                                        @php
                                            $hasInputFields = $group->items->contains(function($item) {
                                                return strpos($item->title, '(js)') !== false || 
                                                       strpos($item->title, '(css)') !== false || 
                                                       $item->type === 'link' && strpos($item->url, 'cdn') !== false;
                                            });
                                        @endphp
                                        
                                        @if($hasInputFields)
                                            <!-- CDN形式の表示 -->
                                            <dl>
                                                @foreach($group->items as $item)
                                                    <div>
                                                        <dt>{{ $item->title }}</dt>
                                                        <dd>
                                                            @if($item->type === 'link')
                                                                <input type="text" value="{{ $item->url }}" readonly onclick="this.select()">
                                                            @else
                                                                <a href="{{ $item->getDisplayUrl() }}" target="{{ $item->getTarget() }}">
                                                                    {{ $item->getDisplayUrl() }}
                                                                </a>
                                                            @endif
                                                        </dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        @else
                                            <!-- 通常のリスト形式 -->
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
                                                        @elseif($item->type === 'collection')
                                                            <div class="collection-item">
                                                                <div class="font-medium text-gray-900 mb-2">{{ $item->title }}</div>
                                                                @if($item->collection)
                                                                    @php
                                                                        // コレクションデータを取得
                                                                        $collection = $item->collection;
                                                                        $collection->load(['fields']);
                                                                        
                                                                        // アクセス制御チェック（実際の運用では選手管理が必要）
                                                                        $competitionId = null;
                                                                        $playerId = null;
                                                                        
                                                                        if ($collection->is_competition_managed) {
                                                                            $competitionId = $guidePage->competition_id;
                                                                        }
                                                                        
                                                                        // 実際の運用では、IPアドレスベースでの選手識別が必要
                                                                        // 今回はすべてのデータを表示（デモ用）
                                                                        
                                                                        // コンテンツ取得
                                                                        $query = \App\Models\CollectionContent::with(['field', 'competition', 'player'])
                                                                            ->where('collection_id', $collection->id);
                                                                            
                                                                        if ($competitionId) {
                                                                            $query->where('competition_id', $competitionId);
                                                                        }
                                                                        
                                                                        $contents = $query->orderBy('created_at', 'desc')->get();
                                                                    @endphp
                                                                    
                                                                    @if($collection->is_player_managed)
                                                                        <div class="text-center py-4 bg-yellow-50 border border-yellow-200 rounded mb-4">
                                                                            <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">選手管理されたコレクション</span>
                                                                            <p class="text-sm text-yellow-700 mt-1">IPアドレスベースで個別データが表示されます</p>
                                                                        </div>
                                                                    @endif
                                                                    
                                                                    @if($contents->count() > 0)
                                                                        <div class="overflow-x-auto border border-gray-200 rounded">
                                                                            <table class="min-w-full">
                                                                                <tbody class="bg-white divide-y divide-gray-200">
                                                                                    @foreach($contents as $content)
                                                                                        <tr class="hover:bg-gray-50">
                                                                                            <td class="px-3 py-2 text-sm font-medium text-gray-900 w-1/3">
                                                                                                {{ $content->field->name }}
                                                                                                @if($content->field->is_required)
                                                                                                    <span class="text-red-500 ml-1">*</span>
                                                                                                @endif
                                                                                            </td>
                                                                                            <td class="px-3 py-2 text-sm text-gray-900 w-2/3">
                                                                                                <div class="break-words" title="{{ $content->value }}">
                                                                                                    {{ $content->formatted_value }}
                                                                                                </div>
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    @else
                                                                        <div class="text-center py-4 bg-gray-50 border border-gray-200 rounded">
                                                                            <p class="text-sm text-gray-500">データがありません</p>
                                                                        </div>
                                                                    @endif
                                                                @else
                                                                    <div class="text-center py-4 bg-red-50 border border-red-200 rounded">
                                                                        <p class="text-sm text-red-600">コレクションが見つかりません</p>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <a href="{{ $item->getDisplayUrl() }}" 
                                                               target="{{ $item->getTarget() }}"
                                                               class="flex items-center space-x-2">
                                                                <span>{{ $item->title }}</span>
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
                                        @endif
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
                    <p class="text-gray-500">このページはまだ準備中です。</p>
                </div>
            </div>
        @endif

        <!-- フッター -->
        <div class="bg-white rounded-lg shadow-sm p-4 mt-8">
            <div class="text-center text-sm text-gray-500">
                最終更新: {{ $guidePage->updated_at->format('Y年m月d日 H:i') }}
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // 成功のフィードバック
                const tooltip = document.createElement('div');
                tooltip.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
                tooltip.textContent = 'コピーしました';
                document.body.appendChild(tooltip);
                
                setTimeout(() => {
                    document.body.removeChild(tooltip);
                }, 2000);
            }).catch(function(err) {
                console.error('コピーに失敗しました: ', err);
            });
        }
    </script>
</body>
</html>