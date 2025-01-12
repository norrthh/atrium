<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBilet extends Model
{
    protected $fillable = ['users_id'];

    public function user(): BelongsTo
    {
       return $this->belongsTo(User::class, 'users_id', 'id');
    }
}
