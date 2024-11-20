<?php

namespace App\Models\Items;

use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
   protected $fillable = [
      'name',
      'idItem',
      'icon',
      'skin',
   ];
}
