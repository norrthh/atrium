<?php

namespace App\Core\Message;

class Message
{
   public static function messages(): array
   {
      return [
         'like_add' => 'Вам успешно начислено {count} монет',
         'like_remove' => 'С вас снято {count} монет',
         'comment_add' => 'Вам успешно начислено {count} монет',
         'comment_remove' => 'С вас снято {count} монет',
         'repost_add' => 'Вам успешно начислено {count} монет',
         'prize_gift' => 'Вам выпал приз {name} количество {count} штук!',
         'event_limit_attempt' => 'Вы уже использовали все попытки',
         'event_last_message' => 'С момента прошлого комментария не прошло более {timeForAttempt} секунд',
         'event_subscription' => 'Для участния необходимо подписаться на {type}',
         'event_lose' => 'Вы не выйграли ничего',
         'event_win_prize' => 'Вы выиграли приз',
         'addAttemptRepost' => 'Вам успешно начислено {count} дополнительных попыток выйграть приз за репост записи',
         'addAttemptLike' => 'Вам успешно начислено {count} дополнительных попыток выйграть приз за лайк записи',
         'event_cumback' => 'Вам добавлено {count} попыток',
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
