<?php

namespace App\Console\Commands;

use App\Models\User\UserMailingLogs;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Console\Command;

class Mailing extends Command
{
   /**
    * The name and signature of the console command.
    *
    * @var string
    */
   protected $signature = 'app:mailing';

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
      $mailings = \App\Models\Mailing::query()->where('status', 0)->get();

      foreach ($mailings as $mailing) {
         $this->info('Mailings ' . $mailing->id . ' send start');
         $telegraphs = TelegraphChat::query()->where('chat_id', '>', 0)->get();

         foreach ($telegraphs as $telegraph) {
            if (!UserMailingLogs::query()->where([['telegraph_id', $telegraph->id], ['mailing_id', $mailing->id]])->first()) {
               $response = TelegraphChat::query()->where('id', $telegraph->id)->first()->message($mailing->text)->send();

               UserMailingLogs::query()->create([
                  'mailing_id' => $mailing->id,
                  'telegraph_id' => $telegraph->id,
                  'response' => $response->json(),
               ]);
            }
         }

         \App\Models\Mailing::query()->where('id', $mailing->id)->update([
            'status' => 1
         ]);

         $this->info('Mailings ' . $mailing->id . ' send end');
      }
   }
}
