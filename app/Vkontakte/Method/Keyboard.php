<?php

namespace App\Vkontakte\Method;

use Illuminate\Support\Facades\Log;

class Keyboard
{
   public function button(string $label, array $payload = [], string $color = 'primary'): array
   {
      $buttons = [
         'action' => [
            'type' => 'text',
            'label' => $label,
         ],
         'color' => $color
      ];

      if (count($payload) > 0) {
         $buttons['action']['payload'] = json_encode($payload);
      }

      Log::info('BUTTON', $buttons);

      return $buttons;
   }

   public function openLink(string $label, string $link): array
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

   public function openApp(string $label): array
   {
      return [
         'action' => [
            'type' => 'open_app',
            'label' => $label,
            'app_id' => 52613695,
            "payload" => '{"button": "0"}',
         ],
      ];
   }

   public function buttons(array $buttonsData): array
   {
      return array_map(function ($buttonData) {
         return self::button($buttonData[0], $buttonData[1]);
      }, $buttonsData);
   }

   public function keyboard(array $buttons, bool $inline = false): string
   {
      return json_encode([
         "one_time" => false,
         'inline' => $inline,
         "buttons" => $buttons
      ]);
   }
}
