<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\CompetitionController;
use App\Http\Controllers\Admin\CompetitionDayController;
use App\Http\Controllers\Admin\CompetitionScheduleController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\CompetitionPlayerController;

// 認証系ルート
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// 管理画面ルート（認証必須）
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // ダッシュボード
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // 大会管理
    Route::resource('competitions', CompetitionController::class);
    
    // 競技日程管理（ネストしたリソース）
    Route::resource('competitions.competition-days', CompetitionDayController::class);
    
    // 競技スケジュール管理
    Route::resource('competition-days.competition-schedules', CompetitionScheduleController::class)
        ->except(['index', 'show'])
        ->parameters([
            'competition-days' => 'competitionDay',
            'competition-schedules' => 'competitionSchedule'
        ]);
    
    // スケジュール順序更新
    Route::patch('competition-days/{competitionDay}/schedules/order', [CompetitionScheduleController::class, 'updateOrder'])
        ->name('competition-schedules.update-order');
    
    // CSVエクスポート・インポート
    Route::get('competition-days/{competitionDay}/schedules/export', [CompetitionScheduleController::class, 'export'])
        ->name('competition-schedules.export');
    Route::post('competition-days/{competitionDay}/schedules/import', [CompetitionScheduleController::class, 'import'])
        ->name('competition-schedules.import');
    
    // 選手管理
    Route::resource('players', PlayerController::class);
    Route::get('players/export', [PlayerController::class, 'export'])->name('players.export');
    Route::post('players/import', [PlayerController::class, 'import'])->name('players.import');
    
    // 大会選手割り当て管理
    Route::resource('competition-players', CompetitionPlayerController::class);
    Route::get('competition-players/export', [CompetitionPlayerController::class, 'export'])->name('competition-players.export');
    Route::post('competition-players/import', [CompetitionPlayerController::class, 'import'])->name('competition-players.import');
    Route::post('competition-players/generate-player-numbers', [CompetitionPlayerController::class, 'generatePlayerNumbers'])
        ->name('competition-players.generate-player-numbers');
});

// ルートアクセス時のリダイレクト
Route::get('/', function () {
    return redirect()->route('admin.home');
});
