<?php

namespace App\Console\Commands;

use App\Models\LastActivity;
use App\Models\User\User;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TestCommand extends Command
{
   /**
    * The name and signature of the console command.
    *
    * @var string
    */
   protected $signature = 'app:activity';

   /**
    * The console command description.
    *
    * @var string
    */
   protected $description = 'Command description';

   /**
    * Execute the console command.
    */
   public function handle()
   {
      $now = Carbon::now();

//      if ($now->isSunday()) {
         $users = User::query()
            ->orderBy('coins_week', 'desc')
            ->where('username_telegram', '!=', '')
            ->orWhere('username_vkontakte', '!=', '')
            ->take(5)
            ->get();

      LastActivity::query()->delete();
      DB::statement('ALTER TABLE `last_activities` AUTO_INCREMENT = 1');

         foreach ($users as $user) {
            LastActivity::query()->create([
               'user_id' => $user->id,
               'count' => $user->coins_week
            ]);
         }

         User::query()->update([
            'coins_week' => 0
         ]);
//      }
   }

}
