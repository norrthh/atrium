<?php

namespace App\Telegram;

use App\Models\LogMessage;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Stringable;

class Handler extends WebhookHandler
{
    const ADMIN_CHAT_ID = '-4547522405';

    public function start()
    {
        $this->chat->message('Сәлам-сәлам! ✨
Если ты хочешь вступить в нашу команду, отправь пожалуйста мне следующую информацию:

1. ФИО
2. возраст, класс, город
3. уровень знания татарского
4. участие в проектах и сменах Сәләт (например, Рухият - 2022, Актив Мәктәбе - 2023..)

Так же не забудь отправить кружок с рассказом о себе и ответом на вопрос «Почему хочешь вступить в команду?» 💌🫶🏻')->send();
    }

    public function handleChatMessage(Stringable $text): void
    {
        if ($this->message->chat()->id() > 0) {
            $count = LogMessage::query()->where('chat_id', $this->message->from()->id())->count();

            if ($count == 1) {
                $firstMessage = LogMessage::query()->where('chat_id', $this->message->from()->id())->first();

                $adminChat = TelegraphChat::query()->where('chat_id', self::ADMIN_CHAT_ID)->first();

                $adminChat->message('Пользователь хочет вступить в команду!')->send();
                $response = $adminChat->forwardMessage($this->message->from()->id(), $firstMessage->message_id)->send();
                $adminChat->forwardMessage($this->message->from()->id(), $this->messageId)->send();

                $this->chat->message('Ожидай рассмотрения! 💌')->send();
                $firstMessage->delete();
            } else {
//               сохранили сообщение с информацией
                LogMessage::query()->create([
                    'chat_id' => $this->message->from()->id(),
                    'message_id' => $this->messageId,
                ]);

                $this->chat->message('Отправь теперь кружочек, чтобы вся информация отправилась на рассмотрение 💌🫶🏻')->send();
            }
        } else {
            if ($this->message->chat()->id() == self::ADMIN_CHAT_ID) {
                if ($this->message->replyToMessage()) {
                    if  ($this->message->replyToMessage()->forwardFrom()) {
                        $user = TelegraphChat::query()->where('chat_id', $this->message->replyToMessage()->forwardFrom()->id())->first();
//
                        $user->message('Пришёл ответ от администратора!')->send();
                        $user->message($text)->send();

                        $this->chat->message("Вы успешно ответили пользователю")->send();
                    } else {
                        $this->chat->message("У пользователя закрыт аккаунт")->send();
                    }
                }
            }
        }
    }
}
