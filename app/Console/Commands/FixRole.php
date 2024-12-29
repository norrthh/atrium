<?php

namespace App\Console\Commands;

use App\Models\User\User;
use App\Models\UserRole;
use Illuminate\Console\Command;

class FixRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-role';

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
        $userRoles = UserRole::query()->get();

        foreach ($userRoles as $userRole) {
           $user = User::query()->where('telegram_id', $userRole->telegram_id)->first();

           if ($user) {
              if ($user->vkontakte_id) {
                 UserRole::query()->where('id', $userRole->id)->update(['vkontakte_id' => $user->vkontakte_id]);
              } else {
                 UserRole::query()->where('id', $userRole->id)->update(['vkontakte_id' => null]);
              }
           } else {
              UserRole::query()->where('id', $userRole->id)->update(['vkontakte_id' => null]);
           }
        }
    }
}
