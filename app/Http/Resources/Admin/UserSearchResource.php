<?php

namespace App\Http\Resources\Admin;

use App\Http\Controllers\Api\v1\SettingController;
use App\Http\Resources\UserResource;
use App\Models\User\UserLogMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
           ... (new UserResource($this->resource))->toArray($request),
           'comment' => $this->vkontakte_id ? UserLogMethod::query()->where([['method', 'like_add'], ['user_id', $this->vkontakte_id]])->count() : 0,
           'like' => $this->vkontakte_id ? (new SettingController())->countLike() : 0,
        ];
    }
}
