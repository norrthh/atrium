<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 */
class NotificationResource extends JsonResource
{
   /**
    * Transform the resource into an array.
    *
    * @return array<string, mixed>
    */
   public function toArray(Request $request): array
   {
      return [
         'id' => $this->id,
         'description' => $this->description,
         'href' => $this->href,
         'time' => $this->time,
         'image' => request()->root() . '/storage/' . $this->image,
         'items' => $this->item->map(function ($item) {
            return [
               'count' => $item->count,
               'item_details' => new ItemResource($item->item),
            ];
         }),
      ];
   }
}
