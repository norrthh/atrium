<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WithdrawUserResource;
use App\Models\User\WithdrawUsers;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
   public function index()
   {
      return WithdrawUserResource::collection(
         WithdrawUsers::query()
            ->where([['user_id', auth()->user()->id], ['status', 0]])
            ->orderBy('id', 'desc')
            ->with('item')
            ->get()
      );
   }

   public function withdraw(Request $request)
   {
      $withdtraw = WithdrawUsers::query()->where([['id', $request->get('id')], ['status', 0]])->first();

      if ($withdtraw) {
         if (WithdrawUsers::query()->where([['user_id', auth()->user()->id], ['status', 1]])->count() < 10) {
            if (auth()->user()->nickname) {
               WithdrawUsers::query()->where('id', $request->get('id'))->update([
                  'status' => 1
               ]);

               return response()->json([
                  'message' => 'Предмет был успешно отправлен на рассмотрение модератами, ожидайте их решения.',
                  'title' => 'Выдача предмета',
                  'icon' => '/ayazik/icons/setting2.svg'
               ]);
            } else {
               return response()->json([
                  'title' => 'привязка аккаунта',
                  'message' => 'К приложению не привязан игровой аккаунт, для вывода предметов, пожалуйста привяжите свой аккаунт через игру.',
                  'icon' => '/ayazik/icons/link2.svg'
               ]);
            }
         } else {
            return response()->json([
               'title' => 'пожалуйста подождите',
               'message' => 'Нельзя вывести предметы, пока в очереди находится больше 10 ваших предметов.',
               'icon' => '/ayazik/icons/setting2.svg'
            ]);
         }
      } else {
         return response()->json([
            'title' => 'пожалуйста подождите',
            'message' => WithdrawUsers::query()->where([['id', $request->get('id')]])->first() ? "Предмет уже выводится" : 'Предмет не найден',
            'icon' => '/ayazik/icons/settings.svg'
         ]);
      }
   }
}
