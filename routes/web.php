<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\CompetitionController;
use App\Http\Controllers\Admin\CompetitionDayController;
use App\Http\Controllers\Admin\CompetitionScheduleController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\CompetitionPlayerController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\CompetitionDeviceController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\ApiTokenController;

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
    Route::get('players/export', [PlayerController::class, 'export'])->name('players.export');
    Route::post('players/import', [PlayerController::class, 'import'])->name('players.import');
    Route::resource('players', PlayerController::class);
    
    // 大会選手割り当て管理
    Route::get('competition-players/export', [CompetitionPlayerController::class, 'export'])->name('competition-players.export');
    Route::post('competition-players/import', [CompetitionPlayerController::class, 'import'])->name('competition-players.import');
    Route::post('competition-players/generate-player-numbers', [CompetitionPlayerController::class, 'generatePlayerNumbers'])
        ->name('competition-players.generate-player-numbers');
    Route::resource('competition-players', CompetitionPlayerController::class);
    
    // 端末管理
    Route::get('devices/export', [DeviceController::class, 'export'])->name('devices.export');
    Route::post('devices/import', [DeviceController::class, 'import'])->name('devices.import');
    Route::resource('devices', DeviceController::class);
    
    // 競技端末割り当て管理
    Route::get('competition-devices/export', [CompetitionDeviceController::class, 'export'])->name('competition-devices.export');
    Route::post('competition-devices/import', [CompetitionDeviceController::class, 'import'])->name('competition-devices.import');
    Route::get('api/competition-devices/player-numbers', [CompetitionDeviceController::class, 'getAvailablePlayerNumbers'])
        ->name('api.competition-devices.player-numbers');
    Route::resource('competition-devices', CompetitionDeviceController::class);
    
    // リソース管理
    Route::get('resources/export', [ResourceController::class, 'export'])->name('resources.export');
    Route::get('resources/{resource}/download', [ResourceController::class, 'download'])->name('resources.download');
    Route::post('resources/{resource}/access-control', [ResourceController::class, 'addAccessControl'])->name('resources.access-control.add');
    Route::delete('resources/{resource}/access-control/{accessControl}', [ResourceController::class, 'removeAccessControl'])->name('resources.access-control.remove');
    Route::resource('resources', ResourceController::class);
    
    // APIトークン管理
    Route::get('api-tokens/export', [ApiTokenController::class, 'export'])->name('api-tokens.export');
    Route::get('api-tokens/debug', function() { return view('admin.api-tokens.debug'); })->name('api-tokens.debug');
    Route::post('api-tokens/{apiToken}/regenerate', [ApiTokenController::class, 'regenerate'])->name('api-tokens.regenerate');
    Route::post('api-tokens/{apiToken}/toggle', [ApiTokenController::class, 'toggle'])->name('api-tokens.toggle');
    Route::resource('api-tokens', ApiTokenController::class);
});

// ルートアクセス時のリダイレクト
Route::get('/', function () {
    return redirect()->route('admin.home');
});
