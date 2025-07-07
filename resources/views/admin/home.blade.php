<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理画面ホーム - SkillJapan Tools</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">SkillJapan Tools 管理画面</h1>
            <div class="flex items-center space-x-4">
                <span>{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-blue-700 hover:bg-blue-800 px-3 py-1 rounded">
                        ログアウト
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">管理画面ホーム</h2>
            <p class="text-gray-600 mb-4">SkillJapan Tools 競技大会管理システムへようこそ</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">スケジュール管理</h3>
                    <p class="text-blue-600">競技大会のスケジュールを管理します</p>
                    <div class="mt-4">
                        <span class="text-sm text-gray-500">（準備中）</span>
                    </div>
                </div>
                
                <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                    <h3 class="text-lg font-semibold text-green-800 mb-2">選手情報管理</h3>
                    <p class="text-green-600">選手の情報を管理します</p>
                    <div class="mt-4">
                        <span class="text-sm text-gray-500">（準備中）</span>
                    </div>
                </div>
                
                <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                    <h3 class="text-lg font-semibold text-purple-800 mb-2">システム管理</h3>
                    <p class="text-purple-600">ユーザーやシステム設定を管理します</p>
                    <div class="mt-4">
                        <span class="text-sm text-gray-500">（準備中）</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded">
                <h4 class="font-semibold text-yellow-800">テスト表示</h4>
                <p class="text-yellow-700 mt-2">管理画面が正常に表示されています。機能は順次追加予定です。</p>
            </div>
        </div>
    </div>
</body>
</html>