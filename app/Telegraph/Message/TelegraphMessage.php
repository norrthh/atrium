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
use Illuminate\Support\Stringable;

class TelegraphMessage extends WebhookHandler
{
   protected WebhookHandler $handler;
   protected UserCore $userCore;

   public function __construct(WebhookHandler $handler)
   {
      $this->handler = $handler;
      $this->userCore = new UserCore();
   }

   public function message(Stringable $text): void
   {
      $replyToMessage = $this->handler->message->replyToMessage();
      $userTelegram = User::query()->where('telegram_id', $this->handler->message->from()->id())->first();
      $userTelegraph = TelegraphChat::query()->where('chat_id', $this->handler->message->from()->id())->first();

      if ($replyToMessage) {
         if ($replyToMessage->from()->id() == '777000' and $userTelegram) {
            $userCore = new UserCore();
            $objectId = (string)$replyToMessage->id();

            if (!$userCore->checkAction($userTelegram->telegram_id, 'wall_reply_new', $objectId)) {
               if ($userTelegraph) {
                  $userCore->setCoin($userTelegram->telegram_id, 'wall_reply_new', 'comment', $objectId, 'telegram_id');
                  $userTelegraph->message(Message::getMessage('comment_add', ['count' => (new CoinInfoCore())->getDataType('comment')]))->send();
               }
            }
         }
      } else {
         $userTelegraph = TelegraphChat::query()->where('chat_id', 891954506)->first();
         $userTelegraph->message(print_r($this->handler->data->toJson(), true))->send();
      }
   }
}
