<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Http\Request;

Route::get('/', function () {
    return Inertia::render('app');
});


Route::get('login', function () {
    return response()->json(['error' => 'Unauthorized'], 401);
})->name('login');

Route::post('/upload', function (Request $request) {
    if ($request->hasFile('file')) {
        $path = $request->file('file');
        $path = Storage::put('telgeram', $path, 'public');
        return response()->json(['url' => $path]);
    }
    return response()->json(['error' => 'File not uploaded'], 400);
});

//Route::post('event')
