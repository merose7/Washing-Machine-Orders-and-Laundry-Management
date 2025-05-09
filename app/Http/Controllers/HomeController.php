<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $machines = \App\Models\Machine::all();
        return view('home', compact('machines')); // pastikan view ini ada di resources/views/home.blade.php
    }
}
