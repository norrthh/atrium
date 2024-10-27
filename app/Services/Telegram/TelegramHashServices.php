<?php

namespace App\Services\Telegram;

class TelegramHashServices
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function checkHash(): bool
    {
        return true;
        $hash = $this->data['hash'] ?? '';
        unset($this->data['hash']);

        ksort($this->data);

        $secretKey = hash('sha256', env('TELEGRAM_BOT_TOKEN'), true);
        $dataString = $this->createStringHash($this->data);
        $calculatedHash = hash_hmac('sha256', $dataString, $secretKey);

        // Выводим для отладки
        return hash_equals($calculatedHash, $hash);
    }

    protected function createStringHash(array $initData): string
    {
        $dataCheckString = '';

        foreach ($initData as $key => $value) {
            if (is_array($value)) {
                // Для вложенных массивов добавляем ключи и значения
                foreach ($value as $subKey => $subValue) {
                    $dataCheckString .= "$key.$subKey=$subValue\n";
                }
            } else {
                $dataCheckString .= "$key=$value\n";
            }
        }

        // Удаление лишних пробелов и новых строк
        return trim($dataCheckString);
    }

    public function sendMessageChannel()
    {

    }
}
