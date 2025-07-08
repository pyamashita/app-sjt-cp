<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * 管理画面のホーム画面を表示
     */
    public function index()
    {
        return view('admin.home');
    }
}
