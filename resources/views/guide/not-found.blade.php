<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ガイドページが見つかりません</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full mx-4">
        <div class="text-center">
            <div class="text-gray-400 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-4">ガイドページが見つかりません</h1>
            @if(isset($competition))
                <p class="text-gray-600 mb-6">
                    {{ $competition->name }} のガイドページは現在公開されていません。
                </p>
            @else
                <p class="text-gray-600 mb-6">
                    現在公開中のガイドページはありません。
                </p>
            @endif
            <div class="text-sm text-gray-500">
                管理者にお問い合わせください。
            </div>
        </div>
    </div>
</body>
</html>