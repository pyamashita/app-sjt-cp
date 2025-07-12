<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $collection->display_name }} - コレクション表示</title>
    <link rel="stylesheet" href="{{ asset('css/guide.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- ヘッダー -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $collection->display_name }}</h1>
                @if($collection->description)
                    <p class="text-lg text-gray-600 mb-4">{{ $collection->description }}</p>
                @endif
                
                <div class="flex justify-center items-center space-x-4">
                    @if($collection->is_competition_managed)
                        <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                            大会ごと管理
                        </span>
                    @endif
                    @if($collection->is_player_managed)
                        <span class="inline-block bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">
                            選手ごと管理
                        </span>
                    @endif
                    @if($emulatePlayer)
                        <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                            {{ $emulatePlayer->name }} として表示中
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @if(isset($needsPlayerSelection) && $needsPlayerSelection)
            <!-- 選手選択が必要 -->
            <div class="bg-white rounded-lg shadow-sm p-8">
                <div class="text-center">
                    <div class="text-yellow-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">選手識別が必要です</h3>
                    <p class="text-gray-500">このコレクションは選手ごとに管理されています。<br>プレビューモードでは選手を選択してください。</p>
                </div>
            </div>
        @else
            <!-- コンテンツ表示 -->
            @if(isset($groupedContents) && $groupedContents->count() > 0)
                <div class="space-y-6">
                    @foreach($groupedContents as $group)
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <!-- グループヘッダー -->
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ $group['key'] }}
                                    @if($group['player'] && $group['competition'])
                                        @php
                                            $competitionPlayer = \App\Models\CompetitionPlayer::where('competition_id', $group['competition']->id)
                                                ->where('player_id', $group['player']->id)
                                                ->first();
                                        @endphp
                                        @if($competitionPlayer && $competitionPlayer->player_number)
                                            <span class="text-sm text-gray-500 ml-2">({{ $competitionPlayer->player_number }})</span>
                                        @endif
                                    @endif
                                </h3>
                                @if($group['competition'])
                                    <p class="text-sm text-gray-500">{{ $group['competition']->name }}</p>
                                @endif
                            </div>

                            <!-- グループ内のコンテンツ -->
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($group['items'] as $item)
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex justify-between items-start mb-2">
                                                <h5 class="text-sm font-medium text-gray-900">{{ $item->field->name }}</h5>
                                                <span class="text-xs text-gray-500">{{ $item->field->content_type_display_name }}</span>
                                            </div>
                                            <div class="text-sm text-gray-700 mb-2">
                                                <div class="break-words" title="{{ $item->value }}">
                                                    {{ $item->formatted_value }}
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $item->updated_at->format('Y/m/d H:i') }}
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    @if($group['items']->count() === 0)
                                        <div class="col-span-full text-center py-4">
                                            <p class="text-sm text-gray-500">このグループにはコンテンツがありません</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- データがない場合 -->
                <div class="bg-white rounded-lg shadow-sm p-8">
                    <div class="text-center">
                        <div class="text-gray-400 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">データがありません</h3>
                        <p class="text-gray-500">このコレクションにはまだデータが登録されていません。</p>
                    </div>
                </div>
            @endif
        @endif

        <!-- フッター -->
        <div class="bg-white rounded-lg shadow-sm p-4 mt-8">
            <div class="flex justify-between items-center text-sm text-gray-500">
                <div>
                    最終更新: {{ $collection->updated_at->format('Y年m月d日 H:i') }}
                </div>
                <div>
                    <a href="javascript:history.back()" class="text-blue-600 hover:text-blue-800">
                        戻る
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>