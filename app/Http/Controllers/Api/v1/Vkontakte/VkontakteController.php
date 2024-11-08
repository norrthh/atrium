<?php

namespace App\Http\Controllers\Api\v1\Vkontakte;

use App\Core\Vkontakte\Webhook\VkontakteWebhook;
use App\Http\Controllers\Controller;
use App\Services\Event\EventKorobkaServices;
use App\Services\EventServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VkontakteController extends Controller
{
   public function confirm(Request $request, VkontakteWebhook $switchServices): string
   {
      Storage::put($request->get('type') . '.json', print_r($request->all(), 1));

      if ($request->get('type') == 'confirmation') {
         return env('VKONTAKTE_CONFIRM');
      }

      return $switchServices->webhook($request->all());
   }

   public function event(Request $request): string
   {
      (new EventServices())->eventVkontakte($request->all(), 'vkontakte');

      return 'ok';
   }
}
