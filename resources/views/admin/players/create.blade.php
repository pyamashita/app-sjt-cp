<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>選手登録 - SkillJapan Tools</title>
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
                    <span class="ml-4 text-gray-600 font-medium">選手登録</span>
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
    <main class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <!-- ヘッダー -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">選手登録</h2>
                    <p class="text-gray-600 mt-1">新しい選手の基本情報を登録します</p>
                </div>
                <a href="{{ route('admin.players.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-400 transition duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    戻る
                </a>
            </div>

            <!-- エラーメッセージ -->
            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- バリデーションエラー -->
            @if ($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 選手登録フォーム -->
            <div class="bg-white shadow-lg rounded-xl p-6">
                <form action="{{ route('admin.players.store') }}" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- 選手名 -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                選手名 <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 都道府県 -->
                        <div>
                            <label for="prefecture" class="block text-sm font-medium text-gray-700 mb-2">
                                都道府県 <span class="text-red-500">*</span>
                            </label>
                            <select id="prefecture" 
                                    name="prefecture" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('prefecture') border-red-500 @enderror">
                                <option value="">選択してください</option>
                                @foreach($prefectures as $prefecture)
                                    <option value="{{ $prefecture }}" {{ old('prefecture') === $prefecture ? 'selected' : '' }}>
                                        {{ $prefecture }}
                                    </option>
                                @endforeach
                            </select>
                            @error('prefecture')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 所属 -->
                        <div>
                            <label for="affiliation" class="block text-sm font-medium text-gray-700 mb-2">
                                所属
                            </label>
                            <input type="text" 
                                   id="affiliation" 
                                   name="affiliation" 
                                   value="{{ old('affiliation') }}"
                                   placeholder="学校名、企業名など"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('affiliation') border-red-500 @enderror">
                            @error('affiliation')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 性別 -->
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                                性別 <span class="text-red-500">*</span>
                            </label>
                            <select id="gender" 
                                    name="gender" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('gender') border-red-500 @enderror">
                                <option value="">選択してください</option>
                                @foreach($genders as $key => $label)
                                    <option value="{{ $key }}" {{ old('gender') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('gender')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- 送信ボタン -->
                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="{{ route('admin.players.index') }}" 
                           class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200">
                            キャンセル
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                            登録
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>