<?php

namespace App\Telegraph\Message;

use App\Core\Action\Coin\CoinInfoCore;
use App\Core\Action\UserCore;
use App\Core\Message\Message;
use App\Models\User\User;
use App\Models\User\UserCoins;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;

class TelegraphMessage extends WebhookHandler
{
   protected WebhookHandler $handler;
   protected UserCore $userCore;

   public function __construct(WebhookHandler $handler)
   {
      $this->handler = $handler;
      $this->userCore = new UserCore();
   }

   public function message(): void
   {
      $replyToMessage = $this->handler->message->replyToMessage();
      if ($replyToMessage) {
//         if ($replyToMessage->from()->id() == '777000') {
            $userCore = new UserCore();

            if (!$userCore->checkAction($this->handler->message->from()->id(), 'wall_reply_new', $replyToMessage->id())) {
               $userTelegram = User::query()->where('telegram_id', $this->handler->message->from()->id())->first();
               $userTelegraph = TelegraphChat::query()->where('chat_id', $this->handler->message->from()->id())->first();
//
               $objectId = (string)$replyToMessage->id();
               if ($userTelegram && $userTelegraph) {
                  $userCore->setCoin($userTelegram->id, 'wall_reply_new', 'comment', $objectId, 'telegram_id');
                  $userTelegraph->message(Message::getMessage('comment_add', ['count' => (new CoinInfoCore())->getDataType('comment')]))->send();
               }
            }
//         }
      }
   }
}
