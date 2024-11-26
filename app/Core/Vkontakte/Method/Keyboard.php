<?php

namespace App\Core\Vkontakte\Method;

class Keyboard
{
   // Метод для создания одной кнопки
   public static function button(string $label, array $payload, string $color = 'primary'): array
   {
      return [
         'action' => [
            'type' => 'text',
            'payload' => json_encode($payload),
            'label' => $label
         ],
         'color' => $color
      ];
   }

   // Метод для создания нескольких кнопок
   public static function buttons(array $buttonsData): array
   {
      return array_map(function ($buttonData) {
         return self::button($buttonData[0], $buttonData[1]);
      }, $buttonsData);
   }

   // Метод для создания inline клавиатуры
   public static function inlineKeyboard(array $buttons, int $length = 2, bool $oneTime = false): string
   {
      return json_encode([
         'one_time' => $oneTime,
         'buttons' => array_chunk($buttons, $length),
         'inline' => true  // Устанавливаем inline на уровне клавиатуры
      ]);
   }

   public static function regularKeyboard(array $buttons, int $length = 2, bool $oneTime = false): string
   {
      return json_encode([
         'one_time' => $oneTime,
         'buttons' => array_chunk($buttons, $length),
      ]);
   }

   // Новый метод для создания клавиатуры с кнопками в одном вызове
   public function createInlineKeyboard(array $buttonsData, int $length = 2, bool $oneTime = false): string
   {
      // Создаем кнопки из данных
      $buttons = self::buttons($buttonsData);
      return self::inlineKeyboard($buttons, $length, $oneTime);
   }

   public function createRegularKeyboard(array $buttonsData, int $length = 2, bool $oneTime = false): string
   {
      $buttons = self::buttons($buttonsData, false);  // Для обычных кнопок inline не устанавливается
      return self::regularKeyboard($buttons, $length, $oneTime);
   }
}
