<?php

namespace App\Vkontakte\Bot;

use App\Models\User\User;
use App\Models\User\UserBilet;

class BotCommandSupportMethod extends BotCommandMethod
{
   public function support(): void
   {
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: '...',
         keyboard: $this->keyboard->keyboard([
            [$this->keyboard->button('🙄 Как получать монетки и билеты?', color: 'positive')],
            [$this->keyboard->button('👾 Не могу привязать аккаунт TG или VK', color: 'positive')],
            [$this->keyboard->button('🌟 Как попасть в ТОП игроков?', color: 'positive')],
            [$this->keyboard->button('Аукционы и магазин пустые', color: 'negative')],
            [$this->keyboard->button('Вернуться в меню', payload: ['main'])],
         ])
      );

      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "👉 Выбери свой вопрос: \n\nНайди нужную информацию и получи ответы на свои вопросы! 😊",
         keyboard: $this->keyboard->keyboard(
            buttons: [
               [$this->keyboard->openApp('Приложение в VK')],
               [$this->keyboard->openLink('Приложение в Telegram', 'https://t.me/atriumappbot')],
            ],
            inline: true
         )
      );
   }
   public function howGet(): void
   {
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message:
            "👉 Это очень просто!
            \nМы начисляем монетки и билеты за каждый лайк, комментарий или репост в нашем сообществе [club33903796|Atrium — Мобильная онлайн-игра!].
            \nНе забудь заходить каждый день, чтобы забрать свои бонусы (без пропусков!) в категории 'Бонусные монеты'.
            \nКаждые 24 часа открывается новый уровень, так что не упусти свою возможность получить больше! 😃"
      );
   }
   public function connectSocial(): void
   {
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "👨‍💻 Выбирай социальную сеть для привязки аккаунта",
         keyboard: $this->keyboard->keyboard(buttons: [
            [$this->keyboard->button(label: "Привязываю в VK", color: 'negative')],
            [$this->keyboard->button(label: "Привязываю в Telegram", color: 'positive')],
            [$this->keyboard->button(label: "Вернуться в меню", payload: ['support'])],
         ])
      );
   }
   public function connectVK(): void
   {
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "
            Чтобы привязать аккаунт, выполните следующие шаги:
            \n⚙️ Перейдите в настройки и сгенерируйте уникальный код.
            \n📱 Откройте приложение Telegram и зайдите в тот же раздел настроек.
            \n🔗 Выберите пункт 'Привязать аккаунт' (тот же, в котором вы генерировали код).
            \n📝 Вставьте сгенерированный код и нажмите кнопку 'Привязать'.
         ",
      );
   }
   public function connectTG(): void
   {
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "
            Чтобы привязать аккаунт, выполните следующие шаги:
            \n⚙️ Перейдите в настройки и сгенерируйте уникальный код.
            \n📱 Откройте приложение ВКонтакте  и зайдите в тот же раздел настроек.
            \n🔗 Выберите пункт 'Привязать аккаунт' (тот же, в котором вы генерировали код).
            \n📝 Вставьте сгенерированный код и нажмите кнопку 'Привязать'.
         ",
      );
   }
   public function sendTopPlayersInfo(): void
   {
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "
            ✨ Чтобы попасть в ТОП игроков, нужно проявлять активность в социальных сетях (ВКонтакте или Телеграм)!
            \n📱 Рейтинг ТОП основывается на статистике за последние 7 дней, так что не упустите шанс!
            \nАктивность проявляется просто: лайки ❤️, комментарии 💬, репосты 🔄 и участие в конкурсах 🎯 приносят вам монеты! 💰💎
            \nТакже не забывайте про раздел с бонусами 🎁 и промокоды! 🔑
            \nУдачи в игре! 🍀
        ",
      );
   }

   public function explainAuctionsAndShop(): void
   {
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "
            👋 Не переживайте, это нормально! Аукционы — это специальная функция, где игроки соревнуются за редкие предметы. 🏆 Тот, кто сделает самую высокую ставку, тот и выиграет! 🎉
            \nНа аукцион добавляется всего один экземпляр предмета, что делает его очень ценным. 💎
            \nЧто касается магазина, он тоже имеет свои особенности. 🛒 Здесь появляются редкие предметы, в основном временные, но иногда и постоянные.
            \nОдин предмет может быть в количестве от 1 до 50 штук. Как только они раскупаются, предмет исчезает из магазина и появится снова только после пополнения администратором. 🌟
            \nНадеемся, это проясняет ситуацию!
            \nУдачи в игре! 🍀
        ",
      );
   }

   public function tickets()
   {
      $user = User::query()->where('vkontakte_id', $this->user)->first();

      if (!$user) {
         $message = 'У вас не зарегестрирован аккаунт в приложение';

         $this->message->sendAPIMessage(
            userId: $this->user_id,
            message: $message,
            keyboard: $this->keyboard->keyboard(
               buttons: [
                  [$this->keyboard->openApp('Приложение в VK')],
               ],
               inline: true
            ),
            conversation_message_id: $this->conversation_message_id
         );
      } else {
         $userBilets = UserBilet::query()->where('users_id', $user->id)->get();
         if (count($userBilets) == 0) {
            $message = 'У вас отсутствуют билеты';
         } else {
            $message = "Ваши билеты:\n";

            foreach ($userBilets as $bilet) {
               $message .= "\n№ " . $bilet->id;
            }
         }

         $this->message->sendAPIMessage(
            userId: $this->user_id,
            message: $message,
            conversation_message_id: $this->conversation_message_id
         );
      }
   }
}
