<?php

namespace App\Console\Commands;

use App\Models\Task\Tasks;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Task extends Command
{
   /**
    * The name and signature of the console command.
    *
    * @var string
    */
   protected $signature = 'app:task';

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
         $tasks = Tasks::query()->where('status', 0)->get();

         foreach ($tasks as $item) {
            if ($item->access['type'] == 1) {
               if (Carbon::parse($item->created_at)->diffInMinutes(now()) >= $item->access['value']) {
                  Tasks::query()->where('id', $item->id)->update([
                     'status' => 1
                  ]);
               }
            }
         }
         sleep(5);
      }
   }
}
