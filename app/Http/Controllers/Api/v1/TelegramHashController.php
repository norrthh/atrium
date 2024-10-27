<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Telegram\TelegramValidationRequest;
use App\Services\Telegram\TelegramHashServices;
use Illuminate\Http\JsonResponse;

class TelegramHashController extends Controller
{
    public function checkHash(TelegramValidationRequest $request): JsonResponse
    {
        return response()->json([
            'status' => (new TelegramHashServices($request->all()))->checkHash()
        ]);
    }
}
