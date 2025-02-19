<?php

namespace App\Vkontakte\Webhook\Hook;

use App\Core\Message\AdminCommands;
use Illuminate\Support\Facades\Log;

class VkontakteChatInviteMethod
{
   public function chatInvite(array $webhook): void
   {
      $data = $webhook['object']['message'];
      if (isset($data) and $data['action']['type'] == 'chat_invite_user') {
         if (!in_array($data['from_id'], (new AdminCommands())->adminsList)) {
            Log::info('200');
         }
      }
   }
}
