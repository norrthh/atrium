<?php

namespace App\Vkontakte\Bot;

class BotCommandPrizeMethod extends BotCommandMethod
{
   public function sendThankYouMessage(): void
   {
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "
            🎉 Отлично! Спасибо за твой лайк и комментарий! 💖
            \nПосле открытия приложения твой стартовый бонус увеличится в 10 раз! 🚀✨
            \nЧто бы ты хотел получить в качестве бонуса? 🎁
        ",
         keyboard: $this->keyboard->keyboard([
            [$this->keyboard->button(label: '1000 монет', color: 'positive')],
            [$this->keyboard->button(label: 'BMW M5 F90 АСХАБА', color: 'positive')],
            [$this->keyboard->button(label: 'MERCEDES GTS ВЕНГАЛБИ', color: 'positive')],
            [$this->keyboard->button(label: 'BMW M4 ЛИТВИНА', color: 'positive')],
            [$this->keyboard->button(label: 'Вернуться в меню', payload: ['main'])],
         ])
      );
   }

   public function sendBonusInfo(): void
   {
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "...",
         keyboard: $this->keyboard->keyboard([
            [$this->keyboard->button(label: 'Вернуться в меню', payload: ['main'])],
         ])
      );

      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "
            🎉 Супер! После релиза игры и регистрации ты сможешь получить все бонусы! 😊
            \nНе забудь забрать их в приложении и проявлять активность в сообществе, чтобы они не сгорели. 🔥
            \nПромокод 👉 freecar нужно активировать там.
            \nТакже участвуй в наших розыгрышах и получай крутые призы! 🎁👇
         ",
         keyboard: $this->keyboard->keyboard(
            buttons: [
               [$this->keyboard->openApp('ПОЛУЧИТЬ ПОДАРОК')]
            ],
            inline: true
         )
      );
   }

   public function sendCarChoiceMessage(): void
   {
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "
            🎉 Отлично! Ты можешь получить любую тачку блогера! 🚗✨
            \nКакую выберешь? 😃
         ",
         keyboard: $this->keyboard->keyboard([
            [$this->keyboard->button(label: '1000 монет', color: 'positive')],
            [$this->keyboard->button(label: 'BMW M5 F90 АСХАБА', color: 'positive')],
            [$this->keyboard->button(label: 'MERCEDES GTS ВЕНГАЛБИ', color: 'positive')],
            [$this->keyboard->button(label: 'BMW M4 ЛИТВИНА', color: 'positive')],
            [$this->keyboard->button(label: 'Вернуться в меню', payload: ['main'])],
         ])
      );
   }

}
