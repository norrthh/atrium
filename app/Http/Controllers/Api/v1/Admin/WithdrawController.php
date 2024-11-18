<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\WithdrawUserResource;
use App\Models\WithdrawUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      $users = WithdrawUsers::query()
         ->where('status', 1)
         ->select('user_id', DB::raw('COUNT(*) as total'))
         ->groupBy('user_id')
         ->orderBy('total', 'desc')
         ->get();

      $userIds = $users->pluck('user_id')->toArray();

      $records = WithdrawUsers::query()
         ->where('status', 0)
         ->whereIn('user_id', $userIds)
         ->with(['user', 'item'])
         ->get()
         ->sortByDesc(function ($record) use ($users) {
            return $users->firstWhere('user_id', $record->user_id)->total ?? 0;
         });

      return WithdrawUserResource::collection($records);
   }


   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      //
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(Request $request)
   {
      //
   }

   /**
    * Display the specified resource.
    */
   public function show(string $id)
   {
      //
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit(string $id)
   {
      //
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, string $id)
   {
      //
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy(string $id)
   {
      //
   }

   public function order(Request $request)
   {
      $request->validate([
         'id' => ['required', 'int', 'exists:withdraw_users,id'],
         'type' => ['required', 'int'],
      ]);

      WithdrawUsers::query()->where('id', $request->get('id'))->update([
         'status' => $request->get('type')
      ]);

      return WithdrawUsers::query()->where('status', 0)->count();
   }

   public function butonOrder()
   {
      $top50Ids = WithdrawUsers::query()
         ->where('status', 0)
         ->select('id', DB::raw('COUNT(*) as total'))
         ->groupBy('user_id', 'id')
         ->orderBy('total', 'desc')
         ->limit(50)
         ->pluck('id'); // Получаем только ID этих записей

      // Обновляем записи по их ID
      WithdrawUsers::query()->whereIn('id', $top50Ids)
         ->update(['status' => 1]); // Пример обновления: ставим статус 1
   }
}
