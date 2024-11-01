<?php

namespace App\Http\Controllers\Api\v1\Event;

use App\Http\Controllers\Controller;
use App\Services\Event\EventKorobkaServices;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function korobka(EventKorobkaServices $services, Request $request)
    {
        $services->create($request->all());
    }
}
