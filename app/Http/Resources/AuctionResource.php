<?php

namespace App\Http\Resources;

use App\Models\UserAuction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionResource extends JsonResource
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
           'item_type' => $this->item_type[0]['type'] ?? null,
           'item_count' => $this->item_type[0]['count'] ?? null,
           'start_price' => UserAuction::query()->where('auction_id', $this->id)->orderBy('id', 'desc')->first()->value ?? $this->start_price,
           'time' => $this->time,
           'item' => new ItemResource($this->item),
           'created_at' => $this->created_at,
           'end_auction' => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->addHours($this->time)->format('Y-m-d H:i:s')
        ];
    }
}
