<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
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
         'idItem' => $this->idItem,
         'name' => $this->name,
         'icon' => request()->root() . '/storage/' . $this->icon,
         'skin' => $this->skin
      ];
   }
}
