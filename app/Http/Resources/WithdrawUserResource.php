<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $count
 * @property mixed $created_at
 * @property mixed $id
 * @property mixed $user
 * @property mixed $item
 */
class WithdrawUserResource extends JsonResource
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
         'user' => new UserResource($this->user),
         'item' => new ItemResource($this->item),
         'count' => $this->count,
         'created_at' => $this->created_at,
         'status' => $this->status
      ];
   }
}
