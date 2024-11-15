<?php

use App\Http\Controllers\Api\v1\ActivityUserController;
use App\Http\Controllers\Api\v1\Admin\AuctionController;
use App\Http\Controllers\Api\v1\Admin\MailingController;
use App\Http\Controllers\Api\v1\Admin\NotificationController;
use App\Http\Controllers\Api\v1\Admin\PromocodeController;
use App\Http\Controllers\Api\v1\Admin\ShopController;
use App\Http\Controllers\Api\v1\Admin\TaskController;
use App\Http\Controllers\Api\v1\Admin\UploadFileController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\CoinsController;
use App\Http\Controllers\Api\v1\ItemsController;
use App\Http\Controllers\Api\v1\Vkontakte\VkontakteController;
use App\Http\Controllers\Api\v1\WithdrawController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::post('auth', [AuthController::class, 'auth']);

//Route::middleware('auth:sanctum')->group(function () {
Route::prefix('activity')->group(function () {
   Route::post('/now', [ActivityUserController::class, 'now']);
   Route::post('/last', [ActivityUserController::class, 'last']);
});

Route::prefix('bonus')->group(function () {
   Route::post('coins', [CoinsController::class, 'index']);
   Route::post('getCoins', [CoinsController::class, 'getCoins']);
});

Route::prefix('tasks')->group(function () {
   Route::post('/', [TaskController::class, 'index']);
});

//   Route::prefix('withdraw')->group(function () {
//      Route::post('/', [WithdrawController::class, 'withdraw']);
//      Route::post('/me', [WithdrawController::class, 'meWithdraw']);
//      Route::post('/all', [WithdrawController::class, 'allWithdraw']);
//   });

//   Route::prefix('promocode')->group(function () {
//      Route::post('/activate', [\App\Http\Controllers\Api\v1\PromocodeController::class, 'activate']);
//   });

Route::prefix('admin')->group(function () {
   Route::prefix('items')->group(function () {
      Route::get('/', [\App\Http\Controllers\Api\v1\Admin\ItemsController::class, 'index']);
      Route::post('/create', [\App\Http\Controllers\Api\v1\Admin\ItemsController::class, 'store']);
      Route::patch('/edit', [\App\Http\Controllers\Api\v1\Admin\ItemsController::class, 'edit']);
      Route::delete('/delete', [\App\Http\Controllers\Api\v1\Admin\ItemsController::class, 'delete']);
   });

   Route::prefix('notifications')->group(function () {
      Route::get('/create', [NotificationController::class, 'store']);
      Route::delete('/delete/{id}', [NotificationController::class, 'destroy']);
   });

   Route::prefix('tasks')->group(function () {
      Route::post('/create', [TaskController::class, 'store']);
      Route::delete('/delete/{id}', [TaskController::class, 'destroy']);
   });

   Route::prefix('shop')->group(function () {
      Route::post('/create', [ShopController::class, 'store']);
      Route::delete('/delete/{id}', [ShopController::class, 'destroy']);
   });

   Route::prefix('auction')->group(function () {
      Route::post('/create', [AuctionController::class, 'store']);
      Route::delete('/delete/{id}', [AuctionController::class, 'destroy']);
   });

   Route::prefix('mailing')->group(function () {
      Route::post('/store', [MailingController::class, 'store']);
   });

   Route::prefix('promocode')->group(function () {
      Route::post('/store', [PromocodeController::class, 'store']);
   });
});

Route::prefix('notifications')->group(function () {
   Route::get('/', [NotificationController::class, 'index']);
});

Route::prefix('shop')->group(function () {
   // buy
   // list
});

Route::post('/upload', [UploadFileController::class, 'uploadFile']);
//});

Route::prefix('items')->group(function () {
   Route::post('getShop', [ItemsController::class, 'getShop']);
   Route::any('getEvent', [ItemsController::class, 'getEvent']);
});
//
//Route::prefix('vkontakte')->group(function () {
//   Route::post('webhook', [VkontakteController::class, 'confirm']);
//   Route::post('event', [VkontakteController::class, 'event']);
//});
