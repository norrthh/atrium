<?php

namespace App\Console\Commands;

use App\Core\Message\Message;
use App\Core\Method\VkontakteMethod;
use App\Models\Event;
use App\Models\EventCumbackPlayer;
use App\Models\EventPrize;
use App\Models\EventPromocode;
use App\Models\EventSocialLogs;
use App\Models\EventUsers;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CumbackPlayerEvent extends Command
{
   /**
    * The name and signature of the console command.
    *
    * @var string
    */
   protected $signature = 'app:cumback-player-event';

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
         $this->filterEvent(EventPrize::query()->where('status', 0)->get());
         $this->filterEvent(EventPromocode::query()->where('status', 0)->get());

         sleep(5);
      }
   }

   protected function storeEventCumback(int $user_id, int $event_id): void
   {
      EventCumbackPlayer::query()->create([
         'user_id' => $user_id,
         'event_id' => $event_id
      ]);
   }
   protected function filterEvent($events): void
   {
      foreach ($events as $item) {
         $event = Event::query()->where('id', $item->event_id)->first();
         if ($event && $event->cumebackPlayer['status'] != 'not_required') {
            $userAttempts = EventUsers::query()->where([['event_id', $event->id], ['countAttempt', '=', 0]])->get();
            foreach ($userAttempts as $userAttempt) {
               if (count(EventCumbackPlayer::query()->where([['user_id', $userAttempt->user_id], ['event_id', $event->id]])->get()) < $event->cumebackPlayer['count']) {
                  $lastCollected = Carbon::parse($userAttempt->updated_at)->setTimezone('Europe/Moscow');
                  $now = Carbon::now('Europe/Moscow');

                  $timePassed = $lastCollected->diffInSeconds($now);

                  if ($timePassed >= $event->cumebackPlayer['time']) {
                     EventUsers::query()->where('id', $userAttempt->id)->update(['countAttempt' => $userAttempt->countAttempt + $event->cumebackPlayer['attempt']]);
                     $this->storeEventCumback($userAttempt->user_id, $userAttempt->user_id);

                     if ($event->social_type == 'vk') {
                        $this->info("Пользователь: {$userAttempt->user_id} успешно получил дополнительную попытку}");
                        (new VkontakteMethod())->sendMessage(
                           User::query()->where('id', $userAttempt->user_id)->first()->vkontakte_id,
                           Message::getMessage('event_cumback', ['count' => $event->cumebackPlayer['attempt']])
                        );
                     }
                  }
               }
            }
         }
      }
   }
}
