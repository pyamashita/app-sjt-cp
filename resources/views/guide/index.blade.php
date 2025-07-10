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
                                                    <li>
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
</body>
</html>