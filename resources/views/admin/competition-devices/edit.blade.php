<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>端末割り当て編集 - SJT-CP</title>
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
                    <a href="{{ route('admin.competition-devices.index') }}" class="ml-4 text-gray-600 hover:text-gray-900">競技端末割り当て</a>
                    <span class="ml-2 text-gray-400">></span>
                    <span class="ml-2 text-gray-600 font-medium">編集</span>
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
                <h2 class="text-3xl font-bold text-gray-900">端末割り当て編集</h2>
                <p class="text-gray-600 mt-2">選手番号を変更してください。</p>
            </div>

            <!-- エラーメッセージ -->
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">入力エラーがあります</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 現在の割り当て情報 -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">現在の割り当て情報</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">大会名</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $competitionDevice->competition->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">開催期間</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $competitionDevice->competition->period }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">端末名</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $competitionDevice->device->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">端末種別</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($competitionDevice->device->type === 'PC') bg-blue-100 text-blue-800
                                    @elseif($competitionDevice->device->type === 'スマートフォン') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $competitionDevice->device->type }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- フォーム -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <div class="px-6 py-8">
                    <form method="POST" action="{{ route('admin.competition-devices.update', $competitionDevice) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- 選手番号編集 -->
                        <div>
                            <label for="player_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                選手番号 <span class="text-red-500">*</span>
                            </label>
                            <div class="flex space-x-4">
                                <input type="text" 
                                       id="player_number" 
                                       name="player_number" 
                                       value="{{ old('player_number', $competitionDevice->player_number) }}"
                                       required
                                       placeholder="例：001、A-01"
                                       class="flex-1 px-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm">
                                @if($availablePlayers->count() > 0)
                                    <select id="player_select" 
                                            onchange="selectPlayer()"
                                            class="px-3 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm">
                                        <option value="">参加選手から選択</option>
                                        @foreach($availablePlayers as $player)
                                            <option value="{{ $player->player_number }}" data-name="{{ $player->player->name }}">
                                                {{ $player->player_number }} - {{ $player->player->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            @error('player_number')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                            @if($availablePlayers->count() > 0)
                                <p class="text-xs text-gray-500 mt-1">
                                    直接入力するか、この大会に登録済みの選手から選択してください。
                                </p>
                            @endif
                        </div>

                        <!-- フォームボタン -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <a href="{{ route('admin.competition-devices.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                                キャンセル
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                更新
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        function selectPlayer() {
            const select = document.getElementById('player_select');
            const input = document.getElementById('player_number');
            if (select.value) {
                input.value = select.value;
            }
        }
    </script>
</body>
</html>