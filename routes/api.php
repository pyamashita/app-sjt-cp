<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ResourceController;

// リソース管理API
Route::prefix('resources')->name('api.resources.')->group(function () {
    // パブリックリソース一覧
    Route::get('/', [ResourceController::class, 'index'])->name('index');
    
    // カテゴリ一覧（{resource}より先に定義）
    Route::get('/categories', [ResourceController::class, 'categories'])->name('categories');
    
    // 統計情報（認証必須）
    Route::get('/stats', [ResourceController::class, 'stats'])->name('stats');
    
    // リソース詳細情報
    Route::get('/{resource}', [ResourceController::class, 'show'])->name('show');
    
    // リソースファイルダウンロード
    Route::get('/{resource}/download', [ResourceController::class, 'download'])->name('download');
    
    // リソースファイルストリーミング
    Route::get('/{resource}/stream', [ResourceController::class, 'stream'])->name('stream');
});