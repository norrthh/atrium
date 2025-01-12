<?php

namespace App\Exports;

use App\Models\User\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
   /**
    * Получение данных для экспорта
    */
   public function collection()
   {
      $users = User::query()
         ->with('user')
         ->get();

      $data = [];
      foreach ($users as $user) {
         for ($i = 0; $i < $user->bilet; $i++) { // Условие на количество билетов
            $data[] = [
               'counter' => null, // Временное значение, позже заменим на корректный
               'username_vkontakte' => $user->user->username_vkontakte,
               'username_telegram' => $user->user->username_telegram,
               'vkontakte_id' => $user->user->vkontakte_id,
               'telegram_id' => $user->user->telegram_id,
            ];
         }
      }

      $shuffled = collect($data)->shuffle();

      return $shuffled->values()->map(function ($item, $index) {
         $item['counter'] = $index + 1;
         return $item;
      });

   }



   /**
    * Заголовки для столбцов Excel
    */
   public function headings(): array
   {
      return [
         'ID',
         'VK Name',
         'Telegram Name',
         'VK ID',
         'Telegram ID',
      ];
   }
}
