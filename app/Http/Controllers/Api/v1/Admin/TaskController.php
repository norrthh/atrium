<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task\TaskItems;
use App\Models\Task\Tasks;
use App\Models\UserTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      return TaskResource::collection(
         Tasks::query()
            ->where('status', 0)
            ->get()
            ->filter(function ($task) {
               if (!UserTask::query()->where('task_id', $task->id)->where('user_id', auth()->user()->id)->exists()) {
                  return $task;
               }
            })
      );
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(Request $request): JsonResponse
   {
      $request->validate([
         'typeSocial' => ['required'],
         'typeTask' => ['required'],
         'href' => ['required'],
         'description' => ['required'],
         'access' => ['required'],
         'items' => ['required'],
      ]);

      $data = $request->all();
      $data['typeTask'] = $data['typeTask'] == 'Подписаться на группу' ? 1 : ($data['typeTask'] == 'Вступить в беседу' ? 2 : 3);
      $data['typeSocial'] = $data['typeSocial'] == 'VK' ? 1 : 2;

      $task = Tasks::query()->create($data);

      TaskItems::query()->create([
         'item_id' => $request->get('items')['id'],
         'task_id' => $task->id,
         'count' => $request->get('items')['count'],
      ]);

      return response()->json(['success' => true]);
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy(string $id): JsonResponse
   {
      Tasks::query()->where('id', $id)->delete();
      return response()->json(['success' => true]);
   }
}
