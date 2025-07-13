<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * フロントページのホーム画面を表示
     */
    public function index()
    {
        return view('frontend.home');
    }

    /**
     * フロントページのWelcome画面を表示（デモ用）
     */
    public function welcome()
    {
        return view('frontend.welcome');
    }
}