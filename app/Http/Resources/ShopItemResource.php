<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopItemResource extends JsonResource
{
   /**
    * Transform the resource into an array.
    *
    * @return array<string, mixed>
    */

   public function __construct($resource)
   {
      parent::__construct($resource);
      static::withoutWrapping(); // Убираем обёртку только для этого ресурса
   }

   public function toArray(Request $request): array
   {
      return [
         'id' => $this->id,
         'item_type' => $this->item_type[0]['type'] ?? null,
         'item_count' => $this->item_type[0]['count'] ?? null,
         'price' => $this->price,
         'count' => $this->count,
         'countActivate' => $this->countActivate,
         'item' => new ItemResource($this->item),
      ];
   }
}
