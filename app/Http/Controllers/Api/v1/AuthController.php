<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\UserAuthenticationServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
   /**
    * @throws ValidationException
    */
   public function auth(Request $request, UserAuthenticationServices $services): JsonResponse
   {
      return response()->json($services->authenticate($request->all()));
   }

   public function avatar(Request $request)
   {
      $botToken = '7799998250:AAHRBKYMK3c7fF3uCVQy5wMZdQisNGMqA-c';
      $userId = $request->get('user_id');
      $photo = $request->get('photo');

      if (isset($photo)) {
            return response()->json([
               'success' => true,
               'message' => 'Фото успешно сохранено!',
               'photo_url' => $photo,
            ]);
      }

      // Получаем информацию о фото пользователя
      $photosResponse = Http::get("https://api.telegram.org/bot{$botToken}/getUserProfilePhotos", [
         'user_id' => $userId
      ]);

      $photosData = $photosResponse->json();
      // Проверяем, есть ли фото профиля
      if (!$photosData['ok'] || $photosData['result']['total_count'] === 0 || empty($photosData['result']['photos'])) {
         return response()->json([
            'success' => false,
            'message' => 'Фото профиля отсутствует.',
         ]);
      }

      $bestPhoto = null;
      $maxWidth = 0;
      foreach ($photosData['result']['photos'] as $photoVariants) {
         foreach ($photoVariants as $photo) {
            if ($photo['width'] > $maxWidth) {
               $bestPhoto = $photo;
               $maxWidth = $photo['width'];
            }
         }
      }

      // Получаем путь к файлу с наилучшим фото
      $fileResponse = Http::get("https://api.telegram.org/bot{$botToken}/getFile", [
         'file_id' => $bestPhoto['file_id']
      ]);

      $fileData = $fileResponse->json();

      if (!$fileData['ok']) {
         return response()->json([
            'success' => false,
            'message' => 'Не удалось получить путь к файлу.',
         ], 500);
      }

      $filePath = $fileData['result']['file_path'];
      $fileUrl = "https://api.telegram.org/file/bot{$botToken}/{$filePath}";

      // Загружаем файл
      $fileContents = file_get_contents($fileUrl);
      $fileName = "{$userId}.jpg";

      // Проверяем, существует ли файл
      if (Storage::disk('public')->exists("photos/{$fileName}")) {
         return response()->json([
            'success' => true,
            'message' => 'Фото уже существует.',
            'photo_url' => Storage::url("photos/{$fileName}"),
         ]);
      }

      // Сохраняем файл, если его нет
      $saved = Storage::disk('public')->put("photos/{$fileName}", $fileContents);

      if ($saved) {
         return response()->json([
            'success' => true,
            'message' => 'Фото успешно сохранено!',
            'photo_url' => Storage::url("photos/{$fileName}"),
         ]);
      }

      return response()->json([
         'success' => false,
         'message' => 'Не удалось сохранить фото профиля.',
      ]);
   }
}
