<?php

namespace App\Console\Commands;

use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpdatePremiumAt extends Command
{
   /**
    * The name and signature of the console command.
    *
    * @var string
    */
   protected $signature = 'app:update-premium-at';

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
      while (true) {
         $premiumUsers = User::query()->where('isPremium', true)
            ->where(function ($query) {
               $query->whereNull('updated_premium_at')
                  ->orWhere('updated_premium_at', '<', Carbon::now()->subDay());
            })
            ->get();

         foreach ($premiumUsers as $user) {
            User::query()->where('id', $user->id)->update([
               'updated_premium_at' => Carbon::now(),
               'coins_week' => 5 + $user->coins_week,
               'coin' => $user->coin + 5
            ]);

            $this->info("Updated updated_premium_at for user {$user->id}");
         }

         $this->info('Updated updated_premium_at for all premium users.');
         sleep(60 * 60);
      }
   }
}
