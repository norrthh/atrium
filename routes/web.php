<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Http\Request;

Route::get('login', function () {
   return response()->json(['error' => 'Unauthorized'], 401);
})->name('login');

Route::get('/', function () {
   return Inertia::render('app');
});
