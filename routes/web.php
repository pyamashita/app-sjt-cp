<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
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
use App\Http\Controllers\Admin\GuidePageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CommitteeMemberController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\ExternalConnectionController;

// 認証系ルート
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ユーザー登録
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// 管理画面ルート（認証必須）
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // ダッシュボード
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // ユーザー管理
    Route::get('users/registrations', [UserController::class, 'registrations'])->name('users.registrations');
    Route::post('users/registrations/{registration}/approve', [UserController::class, 'approveRegistration'])->name('users.registrations.approve');
    Route::post('users/registrations/{registration}/reject', [UserController::class, 'rejectRegistration'])->name('users.registrations.reject');
    Route::post('users/{user}/change-password', [UserController::class, 'changePassword'])->name('users.change-password');
    Route::resource('users', UserController::class);

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
    Route::get('resources/{resource}/serve', [ResourceController::class, 'serve'])->name('resources.serve');
    Route::resource('resources', ResourceController::class);
    
    // APIトークン管理
    Route::get('api-tokens/export', [ApiTokenController::class, 'export'])->name('api-tokens.export');
    Route::get('api-tokens/debug', function() { return view('admin.api-tokens.debug'); })->name('api-tokens.debug');
    Route::get('api-tokens/test', function() { return view('admin.api-tokens.test'); })->name('api-tokens.test');
    Route::get('api-tokens/simple-test', function() { return view('admin.api-tokens.simple-test'); })->name('api-tokens.simple-test');
    
    // テスト用の直接POSTルート
    Route::post('api-tokens-test-store', function(Request $request) {
        return response()->json(['message' => 'POST received', 'data' => $request->all()]);
    })->name('api-tokens.test-store');
    
    // 明示的なstoreルートを最初に定義
    Route::post('api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    
    Route::post('api-tokens/{apiToken}/regenerate', [ApiTokenController::class, 'regenerate'])->name('api-tokens.regenerate');
    Route::post('api-tokens/{apiToken}/toggle', [ApiTokenController::class, 'toggle'])->name('api-tokens.toggle');
    Route::resource('api-tokens', ApiTokenController::class)->except(['store']);
    
    // ガイドページ管理
    Route::post('guide-pages/{guidePage}/activate', [GuidePageController::class, 'activate'])->name('guide-pages.activate');
    Route::get('guide-pages/{guidePage}/preview', [GuidePageController::class, 'preview'])->name('guide-pages.preview');
    Route::post('guide-pages/{guidePage}/sections', [GuidePageController::class, 'addSection'])->name('guide-pages.sections.add');
    Route::post('guide-page-sections/{section}/groups', [GuidePageController::class, 'addGroup'])->name('guide-page-sections.groups.add');
    Route::post('guide-page-groups/{group}/items', [GuidePageController::class, 'addItem'])->name('guide-page-groups.items.add');
    Route::delete('guide-page-sections/{section}', [GuidePageController::class, 'deleteSection'])->name('guide-page-sections.delete');
    Route::delete('guide-page-groups/{group}', [GuidePageController::class, 'deleteGroup'])->name('guide-page-groups.delete');
    Route::delete('guide-page-items/{item}', [GuidePageController::class, 'deleteItem'])->name('guide-page-items.delete');
    Route::put('guide-pages/{guidePage}/sections/order', [GuidePageController::class, 'updateSectionOrder'])->name('guide-pages.sections.order');
    Route::resource('guide-pages', GuidePageController::class);
    
    
    // 競技委員管理
    Route::get('committee-members/export', [CommitteeMemberController::class, 'export'])->name('committee-members.export');
    Route::resource('committee-members', CommitteeMemberController::class);
    
    // メッセージ管理
    Route::post('messages/{message}/resend', [MessageController::class, 'resend'])->name('messages.resend');
    Route::post('messages/{message}/resend-device/{device}', [MessageController::class, 'resendToDevice'])->name('messages.resend-device');
    Route::post('messages/{message}/cancel', [MessageController::class, 'cancel'])->name('messages.cancel');
    Route::post('devices/{device}/test-connection', [MessageController::class, 'testConnection'])->name('devices.test-connection');
    Route::resource('messages', MessageController::class);
    
    // 外部接続設定
    Route::post('external-connections/{externalConnection}/test', [ExternalConnectionController::class, 'test'])->name('external-connections.test');
    Route::resource('external-connections', ExternalConnectionController::class)->only(['index', 'edit', 'update']);
});

// ルートアクセス時のリダイレクト
Route::get('/', function () {
    return redirect()->route('admin.home');
});
