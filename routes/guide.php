<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuideController;

// ガイドページのルート
Route::get('/', [GuideController::class, 'index'])->name('guide.index');
Route::get('/competition/{competition}', [GuideController::class, 'competition'])->name('guide.competition');