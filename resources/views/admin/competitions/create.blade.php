<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規大会作成 - SkillJapan Tools</title>
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
                    <a href="{{ route('admin.competitions.index') }}" class="ml-4 text-gray-600 hover:text-gray-900">大会実施管理</a>
                    <span class="ml-2 text-gray-400">></span>
                    <span class="ml-2 text-gray-600 font-medium">新規作成</span>
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
                <h2 class="text-3xl font-bold text-gray-900">新規大会作成</h2>
                <p class="text-gray-600 mt-2">新しい競技大会の基本情報を入力してください。</p>
            </div>

            <!-- フォーム -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <div class="px-6 py-8">
                    <div id="app">
                        <competition-form
                            form-action="{{ route('admin.competitions.store') }}"
                            cancel-url="{{ route('admin.competitions.index') }}"
                            csrf-token="{{ csrf_token() }}"
                            :is-edit="false"
                            :errors="{{ json_encode($errors->toArray()) }}"
                        ></competition-form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>