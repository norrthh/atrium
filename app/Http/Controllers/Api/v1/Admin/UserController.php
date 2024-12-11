<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
   public function clearBilet(): JsonResponse
   {
      User::query()->where('bilet', '>', 0)->update(['bilet' => 0]);

      return response()->json(['success' => true]);
   }
}
