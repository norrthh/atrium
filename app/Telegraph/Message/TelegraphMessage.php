<?php

namespace App\Telegraph\Message;

use App\Core\Action\Coin\CoinInfoCore;
use App\Core\Action\UserCore;
use App\Core\Message\Message;
use App\Models\ReferralPromocode;
use App\Models\User\User;
use App\Models\User\UserCoins;
use App\Models\UserReferralPromocode;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
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
         if ($userTelegram) {
            $findPromocode = ReferralPromocode::query()->where('name', $text)->first();

            if ($findPromocode) {
               if (!UserReferralPromocode::query()->where('user_id', $userTelegram->id)->exists()) {
                  $userTelegraph
                     ->message('Вы успешно активировали промокод ' . $text . ' выберите тип приза')
                     ->keyboard(Keyboard::make()->buttons([
                        Button::make('Приз 1')->action('promocodeUserPrize')->param('id', 1)->param('promo_id', $findPromocode->id),
                        Button::make('Приз 2')->action('promocodeUserPrize')->param('id', 2)->param('promo_id', $findPromocode->id),
                        Button::make('Приз 3')->action('promocodeUserPrize')->param('id', 3)->param('promo_id', $findPromocode->id),
                     ]))
                     ->send();
               }
            }
         }
      }
   }
}
