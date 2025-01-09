<?php

namespace App\Vkontakte\Bot;

class BotCommandMainMethod extends BotCommandMethod
{
   public function start(): void
   {
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: '...',
         keyboard: $this->keyboard->keyboard(
            buttons: [
               [$this->keyboard->button(label: 'СКАЧАТЬ ИГРУ')],
               [$this->keyboard->button(label: 'ПОМОЩЬ ПО ПРИЛОЖЕНИЮ', payload: ['main'])],
               [$this->keyboard->button(label: 'Получать новости и подарки 💓', color: 'positive')],
               [$this->keyboard->button(label: 'Актуальные вакансии', color: 'negative')],
            ]
         )
      );
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "
                  👋 Привет! Я — чат-бот Гена! 🎉
                  \nМои возможности пока ограничены, но я здесь, чтобы помочь тебе! 😃✨
                  \n👉 Чем могу помочь?
               ",
         keyboard: $this->keyboard->keyboard(
            buttons: [
               [$this->keyboard->openLink('ВСТУПИТЬ В ЧАТ ВКОНТАКТЕ 🥳', 'https://vk.me/join/AJQ1d8G6_CEiFzmZg5Uw6Cn6')],
               [$this->keyboard->openLink('ВСТУПИТЬ В ЧАТ TELEGRAM 💖', 'https://t.me/atriumchat')],
            ],
            inline: true,
         )
      );
   }

   public function download(): void
   {
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: '...',
         keyboard: $this->keyboard->keyboard(
            buttons: [
               [$this->keyboard->button(label: 'Вернуться в главное меню', payload: ['main'])],
            ]
         )
      );

      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "
                  🚀 Скоро у нас большой релиз!
                  \n🎮Скачать нашу супер-игру можно будет на нашем сайте!
                  \n😃Но подождите, мы еще в разработке, и осталось совсем чуть-чуть до выхода нашей топовой игры!
                  \n🌟А пока у нас есть для вас классные конкурсы!
                  \n🎉Каждую неделю мы разыгрываем телефоны, деньги и много других крутых призов!
                  \n🎁Чтобы участвовать, просто стань пользователем нашего приложения в ВКонтакте или Telegram.
                  \n📱💬 Ссылки ниже — не упусти свой шанс! 👇
               ",
         keyboard: $this->keyboard->keyboard(
            buttons: [
               [$this->keyboard->openApp('ПОЛУЧИТЬ ПОДАРКИ')],
            ],
            inline: true
         )
      );
   }
}
