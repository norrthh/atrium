<?php

namespace App\Telegraph\Referral;

use App\Core\Action\UserCore;
use App\Models\ReferralPromocode;
use App\Models\User\User;
use App\Models\UserReferralPromocode;
use App\Services\Referral\ReferralServices;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegraphReferralHandler extends WebhookHandler
{
   protected WebhookHandler $handler;

   public function __construct(WebhookHandler $handler)
   {
      $this->handler = $handler;
   }

   public function promocode(): void
   {
      $user = User::query()->where('telegram_id', $this->handler->callbackQuery->from()->id())->first();
      if ($user) {
         $referralPromocode = ReferralPromocode::query()->where('user_id', $user->id)->first();
         if (!$referralPromocode) {
            $referralPromocode = ReferralPromocode::query()->create([
               'user_id' => $user->id,
               'name' => '#' . (new ReferralServices())->generateUniqueCode()
            ]);
         }

         $userReferrals = UserReferralPromocode::query()->where('referral_promocode_id', $referralPromocode->id)->get();

         $this->handler->chat
            ->message(
               "Ваш промокод: {$referralPromocode->name}" .
               "\nКол-во активации:" . count($userReferrals)
            )
            ->keyboard(
               Keyboard::make()
                  ->button('Пользователи активировавшие промокод')->action('promocode_user')->param('page', 1)
            )
            ->send();
      } else {
         $this->handler->chat->message('Чтобы получить промокод, нужно зарегистрироваться в приложение')->send();
      }
   }

   public function promocode_user(): void
   {
      $user = User::query()->where('telegram_id', $this->handler->callbackQuery->from()->id())->first();

      if ($user) {
         $referralPromocode = ReferralPromocode::query()->where('user_id', $user->id)->first();
         $page = $this->handler->data['page'] ?? 1;
         $userReferrals = (new ReferralServices())->paginate($page, $referralPromocode->id);

         $buttons = [];

         if (count($userReferrals['userReferrals']) > 0) {
            foreach ($userReferrals['userReferrals'] as $item) {
               $buttons[] = Button::make($item->user->username_vkontakte ?? $item->user->username_telegram)->action('return');
            }

            if ($page > 1 and $userReferrals['totalPages'] > 1) {
               $buttons[] = Button::make('Назад')->action('promocode_user')->param('page', $page - 1);
            }

            if ($page + 1 <= $userReferrals['totalPages']) {
               $buttons[] = Button::make('Вперёд')->action('promocode_user')->param('page', $page + 1);
            }
         } else {
            $buttons[] = Button::make('У вас нет реферралов :c')->action('return');
         }

         $this->handler->chat
            ->edit($this->handler->messageId)
            ->message('Ваши реферралы')->keyboard(Keyboard::make()->buttons($buttons))
            ->send();
      }
   }
}
