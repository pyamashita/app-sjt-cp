<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ResourceController;

// リソース管理API
Route::prefix('resources')->name('api.resources.')->middleware(['api.token.optional'])->group(function () {
    // パブリックリソース一覧（認証はオプション）
    Route::get('/', [ResourceController::class, 'index'])->name('index');
    
    // カテゴリ一覧（認証不要）
    Route::get('/categories', [ResourceController::class, 'categories'])->name('categories');
    
    // リソース詳細情報（認証はオプション）
    Route::get('/{resource}', [ResourceController::class, 'show'])->name('show');
    
    // リソースファイルダウンロード（認証はオプション）
    Route::get('/{resource}/download', [ResourceController::class, 'download'])->name('download');
    
    // リソースファイルストリーミング（認証はオプション）
    Route::get('/{resource}/stream', [ResourceController::class, 'stream'])->name('stream');
});

// 認証必須のルート
Route::prefix('resources')->name('api.resources.')->middleware(['api.token.auth:stats'])->group(function () {
    // 統計情報（認証必須）
    Route::get('/stats', [ResourceController::class, 'stats'])->name('stats');
});