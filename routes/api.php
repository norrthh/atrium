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

Route::middleware(\App\Http\Middleware\OwnCors::class)->group(function () {
   Route::post('auth', [AuthController::class, 'auth']);
   Route::post('getAvatar', [AuthController::class, 'avatar']);

   Route::middleware('auth:sanctum')->group(function () {
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
         Route::post('/check', [TaskController::class, 'check']);
      });

      Route::prefix('admin')->group(function () {
         Route::prefix('items')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\v1\Admin\ItemsController::class, 'index']);
            Route::get('/all', [\App\Http\Controllers\Api\v1\Admin\ItemsController::class, 'all']);
            Route::get('/coinbilet', [\App\Http\Controllers\Api\v1\Admin\ItemsController::class, 'coinbilet']);
            Route::post('/create', [\App\Http\Controllers\Api\v1\Admin\ItemsController::class, 'store']);
            Route::post('/edit', [\App\Http\Controllers\Api\v1\Admin\ItemsController::class, 'edit']);
            Route::delete('/delete/{id}', [\App\Http\Controllers\Api\v1\Admin\ItemsController::class, 'destroy']);
         });

         Route::prefix('tasks')->group(function () {
            Route::post('/create', [TaskController::class, 'store']);
            Route::delete('/delete/{id}', [TaskController::class, 'destroy']);
         });

         Route::prefix('notification')->group(function () {
            Route::post('/create', [NotificationController::class, 'store']);
            Route::delete('/delete/{id}', [NotificationController::class, 'destroy']);
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
            Route::post('/create', [MailingController::class, 'store']);
            Route::get('/info', [MailingController::class, 'info']);
         });

         Route::prefix('promocode')->group(function () {
            Route::post('/store', [PromocodeController::class, 'store']);
         });

         Route::prefix('withdraw')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\v1\Admin\WithdrawController::class, 'index']);
            Route::post('/order', [\App\Http\Controllers\Api\v1\Admin\WithdrawController::class, 'order']);
            Route::post('/butonOrder', [\App\Http\Controllers\Api\v1\Admin\WithdrawController::class, 'butonOrder']);
         });

         Route::prefix('users')->group(function () {
            Route::post('/search', [\App\Http\Controllers\Api\v1\Admin\UsersController::class, 'search']);
         });
      });

      Route::prefix('withdraw')->group(function () {
         Route::post('/', [WithdrawController::class, 'index']);
         Route::post('/me', [WithdrawController::class, 'me']);
      });

      Route::prefix('notification')->group(function () {
         Route::post('/ready', [NotificationController::class, 'ready']);
      });

      Route::prefix('shop')->group(function () {
         Route::post('/', [\App\Http\Controllers\Api\v1\ShopController::class, 'index']);
         Route::post('/buyItem', [\App\Http\Controllers\Api\v1\ShopController::class, 'buyItem']);
         // buy
         // list
      });

      Route::prefix('auction')->group(function () {
         Route::post('/', [\App\Http\Controllers\Api\v1\AuctionController::class, 'index']);
         Route::post('/buy', [\App\Http\Controllers\Api\v1\AuctionController::class, 'buy']);
      });

      Route::prefix('tasks')->group(function () {
         Route::get('/', [TaskController::class, 'index']);
      });

      Route::post('/upload', [UploadFileController::class, 'uploadFile']);

      Route::prefix('items')->group(function () {
         Route::post('getShop', [ItemsController::class, 'getShop']);
         Route::any('getEvent', [ItemsController::class, 'getEvent']);
      });

      Route::prefix('setting')->group(function () {
         Route::post('/', [\App\Http\Controllers\Api\v1\SettingController::class, 'index']);
      });

      Route::prefix('/inventory')->group(function () {
         Route::post('/', [\App\Http\Controllers\Api\v1\InventoryController::class, 'index']);
         Route::post('/withdraw', [\App\Http\Controllers\Api\v1\InventoryController::class, 'withdraw']);
      });

      Route::prefix('promocode')->group(function () {
         Route::post('/', [\App\Http\Controllers\Api\v1\PromocodeController::class, 'index']);
         Route::post('/activate', [\App\Http\Controllers\Api\v1\PromocodeController::class, 'activate']);
      });

      Route::prefix('transfer')->group(function () {
         Route::post('/code', [\App\Http\Controllers\Api\v1\TransferController::class, 'code']);
         Route::post('/activate', [\App\Http\Controllers\Api\v1\TransferController::class, 'activate']);
      });
   });

   Route::prefix('vkontakte')->group(function () {
      Route::post('webhook', [VkontakteController::class, 'confirm']);
      Route::post('event', [VkontakteController::class, 'event']);
   });
});
