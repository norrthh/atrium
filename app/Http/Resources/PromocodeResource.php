<?php

namespace App\Http\Resources;

use App\Models\User\UserActivatePromocode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $expiration
 */
class PromocodeResource extends JsonResource
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
      $expiration = $this->expiration[0] ?? null;

      return [
         'id' => $this->id,
         'code' => $this->code,
         'expiration' => $expiration['value'],
         'expiration_type' => $expiration ? (int)$expiration['type'] : null,
         'items' => PromocodeItemResource::collection($this->item),
         'used' => UserActivatePromocode::query()->where('promocode_id', $this->id)->count(),
         'created_at' => $this->created_at
      ];
   }

}
