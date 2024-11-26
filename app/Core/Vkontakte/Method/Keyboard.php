<?php

namespace App\Core\Vkontakte\Method;

class Keyboard
{
   // Метод для создания одной кнопки
   public function button(string $label, array $payload, string $color = 'primary'): array
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

   public function openLink(string $label, string $link, string $color = 'primary'): array
   {
      return [
         'action' => [
            'type' => 'open_link',
            'label' => $label,
            'link' => $link,
            "payload" => '{"button": "0"}',
         ],
      ];
   }

   // Метод для создания нескольких кнопок
   public function buttons(array $buttonsData): array
   {
      return array_map(function ($buttonData) {
         return self::button($buttonData[0], $buttonData[1]);
      }, $buttonsData);
   }

   // Метод для создания inline клавиатуры
   public function inlineKeyboard(array $buttons, int $length = 2, bool $oneTime = false): string
   {
      return json_encode([
         'one_time' => $oneTime,
         'buttons' => array_chunk($buttons, $length),
         'inline' => true  // Устанавливаем inline на уровне клавиатуры
      ]);
   }

   public function regularKeyboard(array $buttons, int $length = 2, bool $oneTime = false): string
   {
      return json_encode([
         "one_time" => false,
         'inline' => true,
         "buttons" => [
            [
               ["action" => [
                  "type" => "open_link",
                  "link" => "https://vk.com/",
                  "label" => "Your URL",
                  "payload" => '{"button": "1"}'],
               ]
            ]]]);
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
