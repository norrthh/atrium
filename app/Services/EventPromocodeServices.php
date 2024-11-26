<?php

namespace App\Services;

use App\Core\EventMethod\EventVkontakteMethod;
use App\Models\Event\Event;
use App\Models\Event\EventPromocode;
use App\Models\Items\Items;
use App\Models\Promocode\Promocode;
use App\Models\Promocode\PromocodeItem;

class EventPromocodeServices
{
   public function promocode()
   {

   }
   protected function store($code, $type, $expiration, $event_id, $countPrize)
   {
      return Promocode::query()->create([
         'code' => $code,
         'promo_type' => $type,
         'expiration' => $expiration,
         'countPrize' => $countPrize,
         'event_id' => $event_id,
      ]);
   }

   public function storePrizes($promo_id, array $prizes)
   {
      foreach ($prizes as $prize) {
         PromocodeItem::query()->create([
            'promocode_id' => $promo_id,
            'item_id' => $prize['item_id'],
            'count' => $prize['count'],
         ]);
      }
   }

   public function create(array $data)
   {
      $promocode = Promocode::query()->where('code', $data['name'])->first();

      if ($promocode) {
         return response()->json([
            'message' => 'Такой промокод уже существует'
         ], 403);
      }

      switch ($data['type_id']) {
         case 1:
            $data['event_id'] = null;
            break;
         case 2:
            $data['text'] = str_replace(
               '{prizes}', Items::query()->where('id', $data['prizes'][0]['id'])->first()->name, // Замена {prizes}
               str_replace('{promocode}', $data['name'], $data['text']) // Замена {promocode}
            );
            $data['post_id'] = (new EventVkontakteMethod())->sendWallMessage($data['image'], $data['text'])['response']['post_id'];
            $data['social_type'] = $data['social'];
            $data['type'] = 5;
            $data['event_id'] = (new EventServices())->store($data)->id;
            break;
      }

      $promo_id = $this->store($data['name'], $data['type_id'], $data['expiration'], $data['event_id'], $data['selectAccessPrize'])->id;
      $this->storePrizes($promo_id, $data['prizes']);

      return [
         'status' => true,
         'message' => 'success'
      ];
   }
}
