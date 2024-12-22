<?php

namespace App\Telegraph\Message;

use App\Core\Action\Coin\CoinInfoCore;
use App\Core\Action\UserCore;
use App\Core\EventMethod\EventTelegramMethod;
use App\Core\Events\EventsServices;
use App\Core\Message\Message;
use App\Models\ChatQuestion;
use App\Models\ReferralPromocode;
use App\Models\User\User;
use App\Models\UserReferralPromocode;
use App\Telegraph\Chat\TelegramChatCommandServices;
use App\Telegraph\Method\UserMessageTelegramMethod;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Cache;
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

   /**
    * @throws \Exception
    */
   public function message(Stringable $text): void
   {
      $userTelegram = $this->getUserByTelegramId();
      $cache = $this->getCache();

      if ($cache) {
         $this->handleCachedStep($cache, $text);
      } else {
         $this->handleIncomingMessage($text, $userTelegram);
      }
   }

   private function getUserByTelegramId(): ?User
   {
      return User::query()->where('telegram_id', $this->handler->message->from()->id())->first();
   }

   private function getCache(): ?array
   {
      return Cache::get('admin_' . $this->handler->message->from()->id());
   }

   private function handleCachedStep(array $cache, Stringable $text): void
   {
      if ($cache['step'] === 1) {
         ChatQuestion::query()->create([
            'question' => $cache['question'],
            'answer' => $text
         ]);

         $this->replyWithMessage("Вы успешно добавили вопрос");
         Cache::delete('admin_' . $this->handler->message->from()->id());
      }
   }

   /**
    * @throws \Exception
    */
   private function handleIncomingMessage(Stringable $text, ?User $userTelegram): void
   {
      $userTelegraph = TelegraphChat::query()->where('chat_id', $this->handler->message->from()->id())->first();;
      $data = $this->handler->request->toArray();

      if (isset($data['message']['reply_to_message'])) {
         $this->handleReplyMessage($data, $text, $userTelegram, $userTelegraph);
      }

      if ($userTelegram) {
         $this->handlePromocodeActivation($text, $userTelegram, $userTelegraph);
      }

      (new TelegramChatCommandServices())->commands(
         $text,
         $this->handler->message->chat()->id(),
         $this->handler->message->id(),
         $this->handler->message->from()->id()
      );
   }

   private function handleReplyMessage(array $data, Stringable $text, ?User $userTelegram, ?TelegraphChat $userTelegraph): void
   {
      $fromId = $this->handler->message->from()->id();
      $messageId = $this->handler->message->id();

      if (isset($data['message']['reply_to_message']['forward_origin']['message_id'])) {
         $postId = $data['message']['reply_to_message']['forward_origin']['message_id'];
         (new EventsServices())->events(
            $postId,
            $fromId,
            $messageId,
            $text,
            new EventTelegramMethod(),
            1,
            $this->handler->message->chat()->id()
         );
      }

      if ($data['message']['reply_to_message']['from']['id'] === 777000 && $userTelegram) {
         $this->processWallReply($data, $userTelegram, $userTelegraph);
      }
   }

   private function processWallReply(array $data, User $userTelegram, ?TelegraphChat $userTelegraph): void
   {
      Log::info('telegram data ' . print_r($data, 1));
//      $objectId = $data['message']['reply_to_message']['id'];
//
//      if (!$this->userCore->checkAction($userTelegram->telegram_id, 'wall_reply_new', $objectId)) {
//         if ($userTelegraph) {
//            $this->userCore->setCoin(
//               $userTelegram->telegram_id,
//               'wall_reply_new',
//               'comment',
//               $objectId,
//               'telegram_id'
//            );
//
//            $userTelegraph->message(
//               Message::getMessage('comment_add', [
//                  'count' => (new CoinInfoCore())->getDataType('comment')
//               ])
//            )->send();
//         }
//      }
   }

   private function handlePromocodeActivation(Stringable $text, User $userTelegram, ?TelegraphChat $userTelegraph): void
   {
      $promo = ReferralPromocode::query()->where('name', $text)->first();

      if ($promo && !$this->hasUserActivatedPromo($userTelegram)) {
         $this->sendPromocodeOptions($text, $promo, $userTelegraph);
      }
   }

   private function hasUserActivatedPromo(User $userTelegram): bool
   {
      return UserReferralPromocode::query()->where('user_id', $userTelegram->id)->exists();
   }

   private function sendPromocodeOptions(Stringable $text, ReferralPromocode $promo, ?TelegraphChat $userTelegraph): void
   {
      if ($userTelegraph) {
         $userTelegraph->message(
            "Вы успешно активировали промокод {$text} выберите тип приза"
         )->keyboard(
            Keyboard::make()->buttons([
               Button::make('Приз 1')->action('promocodeUserPrize')->param('id', 1)->param('promo_id', $promo->id),
               Button::make('Приз 2')->action('promocodeUserPrize')->param('id', 2)->param('promo_id', $promo->id),
               Button::make('Приз 3')->action('promocodeUserPrize')->param('id', 3)->param('promo_id', $promo->id),
            ])
         )->send();
      }
   }

   private function replyWithMessage(string $message): void
   {
      (new UserMessageTelegramMethod())->replyWallComment(
         $this->handler->message->chat()->id(),
         $message,
         $this->handler->message->id()
      );
   }
}
