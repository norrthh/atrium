<?php

namespace App\Http\Controllers\Api\v1\Vkontakte;

use App\Http\Controllers\Controller;
use App\Services\EventServices;
use App\Vkontakte\Webhook\VkontakteWebhook;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VkontakteController extends Controller
{
   public function confirm(Request $request, VkontakteWebhook $switchServices)
   {
//      Storage::put($request->get('type') . '.json', print_r($request->all(), 1));
      if ($request->get('type') == 'confirmation') {
         return env('VKONTAKTE_CONFIRM');
      }

      $switchServices->webhook($request->all());
      return "ok";
   }

   /**
    * @throws ConnectionException
    */
   public function event(Request $request): string
   {
      (new EventServices())->eventVkontakte($request->all(), 'vk');
      return 'ok';
   }
}
