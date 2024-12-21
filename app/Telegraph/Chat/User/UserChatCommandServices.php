<?php

namespace App\Telegraph\Chat\User;

use App\Core\EventMethod\EventTelegramMethod;
use App\Core\EventMethod\EventVkontakteMethod;
use App\Models\ChatLink;
use App\Models\ChatQuestion;
use App\Models\Chats;
use App\Models\ChatWords;
use App\Models\User\User;
use App\Models\UserMute;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserChatCommandServices
{
    /**
     * @throws \Exception
     */
    public function filter(string $text, string $chat_id, $message_id, $user_id): void
    {
        if ($this->checkMute($user_id)) {
            $analyzeText = $this->analyzeText($text);
            if (isset($analyzeText['status']) && $analyzeText['status'] and !UserRole::query()->where('telegram_id', $user_id)->exists()) {
                if (isset($analyzeText['type']) && $analyzeText['type'] == 'links' or isset($analyzeText['type']) && $analyzeText['type'] == 'words') {
                    $user = User::query()->where('telegram_id', $user_id)->first();
                    if ($user) {
                        if ($user->vkontakte_id) {
                            foreach (Chats::query()->get() as $link) {
                                if ($link->messanger == 'vkontakte') {
                                    (new EventVkontakteMethod())->kickUserFromChat($link->chat_id, $user->vkontakte_id);
                                } else {
                                    (new EventTelegramMethod())->kickUserFromChat($link->chat_id, $user_id);
                                }
                            }
                        } else {
                            foreach (Chats::query()->where('messanger', 'telegram')->get() as $link) {
                                (new EventTelegramMethod())->kickUserFromChat($link->chat_id, $user_id);
                            }
                        }

                        (new EventTelegramMethod())->deleteMessage($chat_id, $message_id);
                    } else {
                        foreach (Chats::query()->where('messanger', 'telegram')->get() as $link) {
                            (new EventTelegramMethod())->kickUserFromChat($link->chat_id, $user_id);
                        }
                    }
                }
                if (isset($analyzeText['answer'])) {
                    (new EventTelegramMethod())->replyWallComment($chat_id, $analyzeText['answer'], $message_id);
                }
            }
        } else {
            (new EventTelegramMethod())->deleteMessage($chat_id, $message_id);
        }
    }

    public function analyzeText(string $text): array
    {
        $chatLinks = ChatLink::query()->get();
        $chatWords = ChatWords::query()->get();
        $chatQuestions = ChatQuestion::query()->get();

        foreach ($chatLinks as $chatLink) {
            // Приводим обе строки к нижнему регистру и убираем лишние пробелы и невидимые символы
            $normalizedText = preg_replace('/\s+/', ' ', mb_strtolower(trim($text), 'UTF-8')); // mb_strtolower для корректной работы с русскими буквами
            $normalizedLinkText = preg_replace('/\s+/', ' ', mb_strtolower(trim($chatLink->text), 'UTF-8'));

            // Проверяем, содержит ли нормализованный текст нормализованную ссылку
            if (strpos($normalizedText, $normalizedLinkText) !== false) {
                return [
                    'status' => true,
                    'type' => 'links',
                ];
            }
        }

        foreach ($chatWords as $chatWord) {
            $normalizedText = preg_replace('/\s+/', ' ', mb_strtolower(trim($text), 'UTF-8')); // mb_strtolower для русских символов
            $normalizedWord = preg_replace('/\s+/', ' ', mb_strtolower(trim($chatWord->word), 'UTF-8')); // аналогично для слова

            // Проверяем, содержит ли нормализованный текст нормализованное слово
            if (strpos($normalizedText, $normalizedWord) !== false) {
                return [
                    'status' => true,
                    'type' => 'words',
                ];
            }
        }

        foreach ($chatQuestions as $chatQuestion) {
            $normalizedText = preg_replace('/\s+/', ' ', mb_strtolower(trim($text), 'UTF-8')); // mb_strtolower для русских символов
            $normalizedQuestion = preg_replace('/\s+/', ' ', mb_strtolower(trim($chatQuestion->question), 'UTF-8')); // аналогично для вопроса

            // Проверяем, содержит ли нормализованный текст нормализованный вопрос
            if (strpos($normalizedText, $normalizedQuestion) !== false) {
                return [
                    'status' => true,
                    'answer' => $chatQuestion->answer,
                ];
            }
        }

        return [
            'status' => false,
        ];
    }

    public function checkMute(int $user_id): bool
    {
        $mute = UserMute::query()->where('telegram_id', $user_id)->first();
        if ($mute) {
            if (Carbon::now()->diffInMinutes(Carbon::parse($mute->created_at)) > $mute->time) {
                UserMute::query()->where('telegram_id', $user_id)->delete();
                return true;
            }
            return false;
        }
        return true;
    }
}
