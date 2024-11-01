<?php

use App\Http\Controllers\Api\v1\ActivityUserController;
use App\Http\Controllers\Api\v1\Authentication\AuthController;
use App\Http\Controllers\Api\v1\CoinsController;
use App\Http\Controllers\Api\v1\Coupon\CouponController;
use App\Http\Controllers\Api\v1\Items\ItemsController;
use App\Http\Controllers\Api\v1\TasksController;
use App\Http\Controllers\Api\v1\Vkontakte\VkontakteConfirmationController;
use App\Http\Controllers\Api\v1\WithdrawController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('auth', [AuthController::class, 'auth']);

Route::middleware('auth:sanctum')->group(function () {
//    Route::post('')
   Route::prefix('activity')->group(function () {
      Route::post('/now', [ActivityUserController::class, 'now']);
      Route::post('/last', [ActivityUserController::class, 'last']);
   });

   Route::prefix('bonus')->group(function () {
      Route::post('coins', [CoinsController::class, 'index']);
      Route::post('getCoins', [CoinsController::class, 'getCoins']);
   });

   Route::prefix('tasks')->group(function () {
      Route::post('/', [TasksController::class, 'getTasks']);
   });

   Route::prefix('withdraw')->group(function () {
      Route::post('/', [WithdrawController::class, 'withdraw']);
      Route::post('/me', [WithdrawController::class, 'meWithdraw']);
      Route::post('/all', [WithdrawController::class, 'allWithdraw']);
   });

   Route::prefix('coupons')->group(function () {
      Route::post('/insert', [CouponController::class, 'insert']);
   });
});

Route::prefix('items')->group(function () {
   Route::post('getShop', [ItemsController::class, 'getShop']);
   Route::post('getEvent', [ItemsController::class, 'getEvent']);
});

Route::prefix('event')->group(function () {
   Route::post('/korobka', [\App\Http\Controllers\Api\v1\Event\EventController::class, 'korobka']);
});

Route::prefix('vkontakte')->group(function () {
   Route::post('confirm', [VkontakteConfirmationController::class, 'confirm']);
});
