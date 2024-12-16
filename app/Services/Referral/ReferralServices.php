<?php

namespace App\Services\Referral;

use App\Models\ReferralPromocode;
use App\Models\UserReferralPromocode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReferralServices
{
   public function generateUniqueCode(): string
   {
      do {
         $code = Str::random(16);
      } while (ReferralPromocode::query()->where('name', $code)->exists());

      return $code;
   }

   public function paginate(int $page, int $referralPromocodeId): array
   {
      $perPage = 5;
      $offset = ($page - 1) * $perPage;

      // Основной запрос
      $userReferralsQuery = UserReferralPromocode::query()->where('referral_promocode_id', $referralPromocodeId);

      // Общее количество записей (подсчет один раз!)
      $totalRecords = $userReferralsQuery->count();

      // Общее количество страниц
      $totalPages = ceil($totalRecords / $perPage);

      // Корректировка текущей страницы
      if ($page > $totalPages && $totalPages > 0) {
         $page = $totalPages;
         $offset = ($page - 1) * $perPage;
      }

      // Получение данных для текущей страницы
      $userReferrals = $userReferralsQuery
         ->skip($offset)
         ->take($perPage)
         ->get();

      // Логируем данные
//      Log::info('page:' . $page);
//      Log::info('skip:' . $offset);
//      Log::info('take:' . $perPage);
//      Log::info('totalPages:' . $totalPages);
//      Log::info('totalRecords:' . $totalRecords);
//      Log::info('userReferrals count:' . $userReferrals->count());
//      Log::info('referralPromocodeId:' . $referralPromocodeId);

      return [
         'userReferrals' => $userReferrals,
         'totalPages' => $totalPages,
      ];
   }

}
