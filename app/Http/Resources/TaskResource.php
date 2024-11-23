<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
   /**
    * Transform the resource into an array.
    *
    * @return array<string, mixed>
    */

//'typeSocial',
//'typeTask',
//'href',
//'description',
//'access',
//'status',
//'social_id'
   public function toArray(Request $request): array
   {
      return [
         'id' => $this->id,
         'icon' => $this->typeSocial == 1 ? 'VK' : 'Telegram',
         'task' => $this->typeTask == 1 ? 'Подписаться на группу' : ($this->typeTask == 2 ? 'Вступить в беседу' : 'Подписаться на телеграмм канал'),
         'description' => $this->description,
         'items' => $this->items->map(function ($item) {
            return [
               'count' => $item->count,
               'item_details' => new ItemResource($item->item),
            ];
         }),
         'href' => $this->href,
      ];
   }
}
