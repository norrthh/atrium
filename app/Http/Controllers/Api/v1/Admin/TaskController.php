<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Facades\WithdrawUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task\TaskItems;
use App\Models\Task\Tasks;
use App\Models\User\User;
use App\Models\UserTask;
use App\Services\Telegram\TelegramMethodServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use function Symfony\Component\Translation\t;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Tasks::query()
            ->where('status', 0)
            ->get()
            ->filter(function ($task) {
                if (!UserTask::query()->where('task_id', $task->id)->where('user_id', auth()->user()->id)->exists()) {
                    return $task;
                }
            });

        if (count($tasks) == 0) {
            return response()->json([]);
        }

        return TaskResource::collection($tasks);
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

    public function check(Request $request)
    {
        $request->validate([
            'id' => ['required', 'int'],
        ]);

        $task = Tasks::query()->where('id', $request->get('id'))->first();

        if ($task) {
            if (!UserTask::query()->where([['task_id', $task->id], ['user_id', auth()->user()->id]])->exists()) {
                if ($task->access['type'] == 1 and Carbon::parse($task->created_at)->diffInMinutes(now()) >= $task->access['value']) {
                    return response()->json(['status' => false, 'message' => 'Время выполнения задачи истекло']);
                } else {
                    if ($task->typeTask == 1) {
                        return response()->json(['status' => 403, 'message' => 'Ожидайте обработки задачи']);
                    } elseif ($task->typeTask == 2) {
                        if ($task->typeSocial == 1) {
                            if (auth()->user()->vkontakte_id) {
                                if ($this->checkUserInChat($task->social_id, auth()->user()->vkontakte_id)) {
                                    UserTask::query()->create([
                                        'task_id' => $task->id,
                                        'user_id' => auth()->user()->id
                                    ]);

                                    $taskItem = TaskItems::query()->where('task_id', $task->id)->first();
                                    WithdrawUser::store($taskItem->item_id, $taskItem->count);

                                    return response()->json(['status' => true, 'message' => 'Вам успешно начислены бонусы за эту задачу']);
                                } else {
                                    return response()->json(['status' => false, 'message' => 'Вы не вступили в беседу']);
                                }
                            } else {
                                return response()->json(['status' => false, 'message' => 'У вас не привязан вк аккаунт']);
                            }
                        }
                        return response()->json(['status' => 403, 'message' => 'Ожидайте обработки задачи']);
                    } elseif ($task->typeTask == 3) {
                        if ($task->typeSocial == 1) {
                            return response()->json(['status' => 403, 'message' => 'Ожидайте обработки задачи']);
                        } else {
                            if (auth()->user()->telegram_id) {
                                $subscription = (new TelegramMethodServices())->getChatMember(auth()->user()->telegram_id, $task->social_id);
                                if (isset($subscription['result']) && $subscription['result']['status'] != 'left') {
                                    UserTask::query()->create([
                                        'task_id' => $task->id,
                                        'user_id' => auth()->user()->id
                                    ]);

                                    $taskItem = TaskItems::query()->where('task_id', $task->id)->first();
                                    WithdrawUser::store($taskItem->item_id, $taskItem->count);

                                    return response()->json(['status' => true, 'message' => 'Вам успешно начислены бонусы за эту задачу']);
                                } else {
                                    return response()->json(['status' => false, 'message' => 'Вы не подписали на телеграмм канал']);
                                }
                            } else {
                                return response()->json(['status' => false, 'message' => 'У вас не привязан телеграмм аккаунт']);
                            }
                        }
                    }
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Бонусы за эту задачу уже были выданы, возможно, вы уже выполнили ее или бонусы были выданы автоматический']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Задача не найдена']);
        }
    }

    public function checkUserInChat($chatId, $userId): bool
    {
        $accessToken = env('VKONTAKTE_TOKEN');  // Ваш токен доступа ВКонтакте

        $url = "https://api.vk.com/method/messages.getConversationMembers?peer_id=$chatId&access_token=$accessToken&v=" . env('VKONTAKTE_VERSION');

        $response = file_get_contents($url);
        $data = json_decode($response, true);
        if (isset($data['response']['items'])) {
            foreach ($data['response']['items'] as $item) {
                if ($item['member_id'] == $userId) {
                    return true;
                }
            }

            return false;
        } else {
            return false;
        }
    }
}
