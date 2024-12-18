<?php

namespace App\Core\Message;

class Message
{
   public static function messages(): array
   {
      return [
         'like_add' => 'Вы получили {count} монет(у)! &#128515;',
         'like_remove' => '&#129301; С вас снято {count} монет(а)',
         'comment_add' => 'Вам начислено {count} монет(а)! &#128523;',
         'comment_remove' => '&#129301; С вас снято {count} монет(а)',
         'repost_add' => 'Вам начислено {count} монет(а)! &#128523;',
         'prize_gift' => 'Вам выпал приз {name} количество {count} штук(а)!',
         'event_limit_attempt' => 'Вы использовали все попытки &#129301;',
         'event_last_message' => 'С момента прошлого ответа прошло {timeForAttempt} секунд, подождите немного &#129303;',
         'event_subscription' => '&#128172; Для участния необходимо подписаться на {type}',
         'event_lose' => 'Мимо, попробуйте ещё! &#128406;',
         'event_win_prize' => 'Вы выиграли приз! &#129497;',
         'addAttemptRepost' => 'Вам начислено {count} дополнительных попыток выйграть приз за репост записи! &#129303;',
         'addAttemptLike' => 'Вам начислено {count} дополнительных попыток выйграть приз за лайк записи! &#129303;',
         'event_cumback' => '&#129303; Вам добавлено {count} попыток!',
         'event_lose_prize' => 'Призы уже закончились &#129301; Ожидайте еще мероприятяи!',
      ];
   }

   public static function getMessage(string $path, array $data = []): string
   {
      $message = self::messages()[$path] ?? '';

      foreach ($data as $key => $value) {
         $message = str_replace("{" . $key . "}", $value, $message);
      }

      return $message;
   }
}
