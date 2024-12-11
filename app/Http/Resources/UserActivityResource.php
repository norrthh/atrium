<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       $avatar = $this->avatar_telegram ?: ($this->avatar ?: '/ayazik/no_image.png');
       return [
          'id' => $this->id,
          'username' => $this->username_telegram ?: ($this->username_vkontakte ?: $this->nickname),
          'nickname' => $this->nickname,
          'coin' => $this->coins_week ?? 0,
          'bilet' => $this->bilet ?? 0,
          'avatar' => filter_var($avatar, FILTER_VALIDATE_URL) ? $avatar : request()->root() . $avatar,
          'created_at' => $this->created_at,
          'connect_social' => $this->telegram_id and $this->vkontakte_id,
          'tg_name' => $this->username_telegram,
          'vk_name' => $this->username_vkontakte
       ];
    }
}
