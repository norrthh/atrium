<?php

namespace App\Vkontakte\Webhook\Hook;

use App\Facades\WithdrawUser;
use App\Models\Task\TaskItems;
use App\Models\Task\Tasks;
use App\Models\User\User;
use App\Models\User\UserTask;
use Illuminate\Support\Carbon;

class VkontakteGroupMethod
{
   public function groupJoin(array $data)
   {
      $groupId = $data['group_id'];

      $task = Tasks::query()->where([['social_id', $groupId], ['status', 0]])->first();
      if ($task) {
         $user = User::query()->where('vkontakte_id', $data['object']['user_id'])->first();
         if ($user) {
            if (!UserTask::query()->where([['task_id', $task->id], ['user_id', $user->id]])->exists()) {
               if ($task->access['type'] == 1 and Carbon::parse($task->created_at)->diffInMinutes(now()) >= $task->access['value']) {
                  return;
               } else {
                  UserTask::query()->create([
                     'task_id' => $task->id,
                     'user_id' => $user->id
                  ]);

                  $taskItem = TaskItems::query()->where('task_id', $task->id)->first();
                  WithdrawUser::store($taskItem->item_id, $taskItem->count, $user->id);
               }
            }
         }
      }
   }
}
