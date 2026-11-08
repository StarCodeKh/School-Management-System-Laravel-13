<?php

namespace App\Http\Controllers;

class SettingController extends Controller
{
    // index page setting
    public function index()
    {
        return view('setting.settings');
    }
}
