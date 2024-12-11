<?php

namespace App\Telegraph\Referral;

use App\Core\Action\UserCore;
use App\Facades\WithdrawUser;
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

   public function promocodeUserPrize(): void
   {
      $prizes = [
         1 => [
            'Приз 1',
            'Приз 2',
            'Приз 3',
         ],
         2 => [
            'Приз 3',
            'Приз 4',
            'Приз 5',
         ],
         3 => [
            'Приз 6',
            'Приз 7',
            'Приз 8',
         ],
      ];

      $this->handler->chat
         ->edit($this->handler->messageId)
         ->message(
            "Вы выбрали приз " . $this->handler->data['id'] .
            "\nТип призов:\n" . implode("\n", $prizes[$this->handler->data['id']])
         )
         ->keyboard(Keyboard::make()->buttons([
            Button::make('Выбрать приз' . ($this->handler->data['id'] == 1 ? 2 : ($this->handler->data['id'] == 2 ? 3 : 1)))
               ->action('promocodeUserPrizeActivate')
               ->param('id', ($this->handler->data['id'] == 1 ? 2 : ($this->handler->data['id'] == 2 ? 3: 1)))->param('promo_id', $this->handler->data['promo_id']),

            Button::make('Выбрать приз' . ($this->handler->data['id'] == 1 ? 3 : ($this->handler->data['id'] == 2 ? 1 : 2)))
               ->action('promocodeUserPrizeActivate')
               ->param('id', $this->handler->data['id'] == 1 ? 3 : ($this->handler->data['id'] == 2 ? 1 : 2))->param('promo_id', $this->handler->data['promo_id']),

            Button::make('Активировать')->action('promocodeUserPrizeActivate')->param('id', $this->handler->data['id'])->param('promo_id', $this->handler->data['promo_id']),
         ]))
         ->send();
   }

   public function promocodeUserPrizeActivate(): void
   {
      $userTelegram = User::query()->where('telegram_id', $this->handler->callbackQuery->from()->id())->first();

      if (!UserReferralPromocode::query()->where('user_id', $userTelegram->id)->exists()) {
         $prizes = [
            1 => [1, 2, 3],
            2 => [1, 2, 3],
            3 => [1, 2, 3],
         ];

         foreach ($prizes[$this->handler->data['id']] as $prize) {
            WithdrawUser::store($prize, 1, $userTelegram->id);

         }

         UserReferralPromocode::query()->create([
            'user_id' => $userTelegram->id,
            'referral_promocode_id' => $this->handler->data['promo_id']
         ]);

         $this->handler->chat->edit($this->handler->messageId)->message('Вы успешно активировали приз')->send();
      } else {
         $this->handler->chat->edit($this->handler->messageId)->message('Вы уже активировали приз')->send();
      }
   }
}
