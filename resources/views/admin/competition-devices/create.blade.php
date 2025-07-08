<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>端末割り当て - SJT-CP</title>
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
                    <span class="ml-2 text-gray-600 font-medium">新規割り当て</span>
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
                <h2 class="text-3xl font-bold text-gray-900">端末割り当て</h2>
                <p class="text-gray-600 mt-2">選手用端末を大会の選手番号に割り当ててください。</p>
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

            <!-- フォーム -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <div class="px-6 py-8">
                    <form method="POST" action="{{ route('admin.competition-devices.store') }}" class="space-y-6">
                        @csrf

                        <!-- 大会選択 -->
                        <div>
                            <label for="competition_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                大会 <span class="text-red-500">*</span>
                            </label>
                            <select id="competition_id" 
                                    name="competition_id" 
                                    required
                                    onchange="updateAvailablePlayers()"
                                    class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm">
                                <option value="">選択してください</option>
                                @foreach($competitions as $competition)
                                    <option value="{{ $competition->id }}" 
                                            {{ old('competition_id', $selectedCompetition?->id) == $competition->id ? 'selected' : '' }}>
                                        {{ $competition->name }} ({{ $competition->period }})
                                    </option>
                                @endforeach
                            </select>
                            @error('competition_id')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 端末選択 -->
                        <div>
                            <label for="device_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                端末 <span class="text-red-500">*</span>
                            </label>
                            <select id="device_id" 
                                    name="device_id" 
                                    required
                                    class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm">
                                <option value="">選択してください</option>
                                @foreach($devices as $device)
                                    @if(!$assignedDevices->contains($device->id))
                                        <option value="{{ $device->id }}" {{ old('device_id') == $device->id ? 'selected' : '' }}>
                                            {{ $device->name }} ({{ $device->type }})
                                            @if($device->ip_address) - {{ $device->ip_address }} @endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('device_id')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">
                                選手用端末のみ表示されています。すでに割り当て済みの端末は除外されています。
                            </p>
                        </div>

                        <!-- 選手番号入力 -->
                        <div>
                            <label for="player_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                選手番号 <span class="text-red-500">*</span>
                            </label>
                            <div class="flex space-x-4">
                                <input type="text" 
                                       id="player_number" 
                                       name="player_number" 
                                       value="{{ old('player_number') }}"
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                割り当て
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

        function updateAvailablePlayers() {
            const competitionId = document.getElementById('competition_id').value;
            const playerSelect = document.getElementById('player_select');
            
            if (!competitionId) {
                if (playerSelect) {
                    playerSelect.innerHTML = '<option value="">参加選手から選択</option>';
                }
                return;
            }

            // AJAX でその大会の選手一覧を取得
            fetch(`/admin/api/competition-devices/player-numbers?competition_id=${competitionId}`)
                .then(response => response.json())
                .then(data => {
                    if (playerSelect) {
                        playerSelect.innerHTML = '<option value="">参加選手から選択</option>';
                        data.forEach(player => {
                            const option = document.createElement('option');
                            option.value = player.player_number;
                            option.textContent = `${player.player_number} - ${player.player_name}`;
                            playerSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching players:', error);
                });
        }
    </script>
</body>
</html>