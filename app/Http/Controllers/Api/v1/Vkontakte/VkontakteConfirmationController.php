<?php

namespace App\Http\Controllers\Api\v1\Vkontakte;

use App\Http\Controllers\Controller;
use App\Services\Event\EventKorobkaServices;
use App\Vkontakte\VkontakteSwitchServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VkontakteConfirmationController extends Controller
{
    public function confirm(Request $request, VkontakteSwitchServices $switchServices): string
    {
        Storage::put($request->get('type') . '.json', print_r($request->all(), 1));

        if ($request->get('type') == 'confirmation') {
            return env('VKONTAKTE_CONFIRM');
        }

        Log::info('Vkontakte data 2: ');
        $switchServices->switchData($request->all());
        return 'ok';
    }

    public function vkontakteKorobka(Request $request)
    {
        return (new EventKorobkaServices())->vkontakte($request->all());
    }
}
