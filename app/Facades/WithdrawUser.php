<?php

namespace App\Facades;

use App\Services\WithdrawUserServices;
use Illuminate\Support\Facades\Facade;

/**
 * @method static store(string $item_id, string $count, int|null $user_id): void
 */
class WithdrawUser extends Facade
{
   protected static function getFacadeAccessor(): string
   {
      return WithdrawUserServices::class;
   }
}
