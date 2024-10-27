<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppController extends Controller
{
    public function index()
    {
        return Inertia::render('app', [
            'winners' => [
                'last_winners' => [],
//                'winners' => User::query()->orderBy('')
            ]
        ]);
    }

    public function auth(): Response
    {
        return Inertia::render('auth');
    }
}
