<?php

namespace App\Vkontakte\Bot;

class BotCommandVacancyMethod extends BotCommandMethod
{
   public function sendVacancyInfo(): void
   {
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "...",
         keyboard: $this->keyboard->keyboard([
            [$this->keyboard->button(label: 'Назад', payload: ['main'])],
         ])
      );

      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "
            🌟 Мы создали паблик с актуальными вакансиями! 🙌
            \nНадеемся на твою помощь в развитии — каждый человек для нас ценен! 💖
         ",
         keyboard: $this->keyboard->keyboard(
            buttons: [
               [$this->keyboard->openLink('Посмотреть вакансии', 'https://vk.com/atriumdev')],
            ],
            inline: true
         )
      );
   }

}
