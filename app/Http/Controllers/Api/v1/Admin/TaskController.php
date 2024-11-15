<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task\Tasks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      return Tasks::query()->get();
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(Request $request)
   {
      $request->validate([
         'typeSocial' => ['required'],
         'typeTask' => ['required'],
         'href' => ['required'],
         'description' => ['required'],
         'access' => ['required'],
      ]);

      Tasks::query()->create($request->all());
      return response()->json(['success' => true]);
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy(string $id): JsonResponse
   {
      Tasks::query()->where('id', $id)->delete();
      return response()->json(['success' => true]);
   }
}
