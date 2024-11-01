<?php

namespace App\Vkontakte\Events;

use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventUsers;
use App\Models\EventVkontakteLog;
use Illuminate\Support\Carbon;

class VkontakteEventKorobkaServices extends VkontakteEventsServices
{
    public function korobka(array $data): void
    {
        $findEvent = Event::query()->where('post_id', $data['object']['post_id'])->with('korobka')->first();

        if ($findEvent) {
            $eventUser = EventUsers::query()->where([['user_id', $data['object']['from_id']], ['event_id', $findEvent->id]])->first();

            if ($eventUser && $eventUser->countAttempt > $findEvent->korobka->countAttempt) {
                $this->replyToComment($data['object']['post_id'], 'Вы уже использовали все попытки', $data['object']['id']);
                die();
            } elseif (!$eventUser) {
                EventUsers::query()->create([
                    'user_id' => $data['object']['from_id'],
                    'event_id' => $findEvent->id,
                    'countAttempt' => $findEvent->korobka->countAttempt - 1
                ]);
            } elseif ($eventUser->countAttempt < $findEvent->korobka->countAttempt) {
                EventUsers::query()->where('id', $eventUser->id)->update([
                    'countAttempt' => $eventUser->countAttempt - 1
                ]);
            }

            if (count($findEvent->korobka->attempts) > EventPrize::query()->where('event_id', $data['object']['post_id'])->count()) {
                if ($data['object']['text'] == $findEvent->korobka->word) {

                    $lastMessage = EventVkontakteLog::query()->where([['post_id', $data['object']['post_id']]])->orderBy('id', 'desc')->first();

                    if ($lastMessage) {
                        $lastCollected = Carbon::parse($lastMessage->created_at)->setTimezone('Europe/Moscow');
                        $now = \Carbon\Carbon::now('Europe/Moscow');

                        if ($lastCollected->diffInSeconds($now) < $findEvent->korobka->timeForAttempt) {
                            $this->replyToComment($data['object']['post_id'], 'С момента прошлого комментария не прошло более ' . $findEvent->korobka->timeForAttempt . ' сек', $data['object']['id']);
                            die();
                        }
                    }

                    $this->logUser($data['object']['from_id'], $data['object']['post_id'], $findEvent->id);

                    if ($findEvent->korobka->subscribe and !$this->checkSubscriptionGroup($data['object']['from_id'])) {
                        $this->replyToComment($data['object']['post_id'], 'Для участния необходимо подписаться на группу', $data['object']['id']);
                        die();
                    }

                    if ($findEvent->korobka->subscribe_mailing and !$this->checkSubscriptionMailing($data['object']['from_id'])) {
                        $this->replyToComment($data['object']['post_id'], 'Для участния необходимо подписаться на группу', $data['object']['id']);
                        die();
                    }

                    if (EventVkontakteLog::query()->where([['post_id', $data['object']['post_id']]])->count() >= $findEvent->korobka->countMessage + 1) {
                        if ($this->calculatePrize(EventVkontakteLog::query()->where('post_id', $data['object']['post_id'])->count(), $findEvent->korobka->countAttempt)) {
                            if ($this->winPrize($data['object']['from_id'], $data['object']['post_id'], $findEvent->korobka->attempts)) {
                                $this->replyToComment($data['object']['post_id'], 'Вы выиграли ' . $findEvent->korobka->text, $data['object']['id'], $findEvent->korobka->bg['successBackground']);
                            } else {
                                $this->replyToComment($data['object']['post_id'], 'Вы не выйграли ничего', $data['object']['id']);
                            }
                        } else {
                            $this->replyToComment($data['object']['post_id'], 'Вы не выйграли ничего', $data['object']['id']);
                        }
                    } else {
                        $this->replyToComment($data['object']['post_id'], 'Вы не выйграли ничего', $data['object']['id']);
                    }
                }
            } else {
                $this->replyToComment($data['object']['post_id'], 'Вы не выйграли ничего', $data['object']['id']);
                $this->closeComments($data['object']['post_id']);
            }
        }
    }
}
