<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Models\UserTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TransferController extends Controller
{
   public function code(Request $request)
   {
      $user = User::query()->where('id', auth()->user()->id)->first();

      if (!$user->telegram_id or !$user->vkontakte_id) {
         UserTransfer::query()->where('user_id', auth()->user()->id)->delete();

         $str = Str::random(16);

         UserTransfer::query()->create([
            'user_id' => auth()->user()->id,
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'code' => $str
         ]);

         return response()->json([
            'code' => $str
         ]);
      }
   }

   public function activate(Request $request)
   {
      $userFind = UserTransfer::query()->where([['code', $request->get('code')], ['to', $request->get('to')], ['from', $request->get('from')]])->first();


      if ($userFind) {
         $user = User::query()->where('id', $userFind->user_id)->first();

         if ($user) {
            $this->updateUserIds($userFind->user_id, auth()->user()->id);

            User::query()->where('id', auth()->user()->id)->update([
               'coin' => $user->coin + auth()->user()->coin,
               'bilet' => $user->bilet + auth()->user()->bilet,
            ]);

            if ($userFind->to == 'vkontakte') {
               User::query()->where('id', auth()->user()->id)->update([
                  'vkontakte_id' => $user->vkontakte_id ?? null,
                  'username_vkontakte' => $user->username_vkontakte ?? null,
                  'avatar' => $user->avatar ?? null,
               ]);
            } else {
               User::query()->where('id', auth()->user()->id)->update([
                  'telegram_id' => $user->telegram_id ?? null,
                  'username_telegram' => $user->username_telegram ?? null,
                  'avatar_telegram' => $user->avatar_telegram ?? null,
               ]);
            }

            User::query()->where('id', $userFind->user_id)->delete();
//            $userFind->delete();

            return response()->json([
               'status' => true,
               'message' => 'Аккаунт успешно перенесен'
            ]);
         }

         return response()->json([
            'status' => false,
            'message' => 'Код не верен'
         ]);
      }

      return response()->json([
         'status' => false,
         'message' => 'Код не верен'
      ]);
   }

   public function updateUserIds($oldUserId, $newUserId)
   {
      $tables = DB::select('SHOW TABLES');

      foreach ($tables as $table) {
         $tableName = $table->{'Tables_in_' . env('DB_DATABASE')};

         $columns = Schema::getColumnListing($tableName);

         if (in_array('user_id', $columns)) {
            DB::table($tableName)
               ->where('user_id', $oldUserId)
               ->update(['user_id' => $newUserId]);
         }
//         echo  $tableName;
      }
   }
}
