<?php

namespace App\Services;

use App\Models\LogMessage;

class LogServices
{
    public static function log(int $user_id, int $post_id, int $typeMessage): void
    {
        LogMessage::query()->create([
            'user_id' => $user_id,
            'post_id' => $post_id,
            'typeMessage' => $typeMessage
        ]);
    }
}
