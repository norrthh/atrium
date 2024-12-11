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
      return User::query()->select('id', 'username_vkontakte', 'username_telegram', 'vkontakte_id', 'telegram_id', 'bilet')->where('bilet', '>', 0)->get();
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
         'Билет',
      ];
   }
}
