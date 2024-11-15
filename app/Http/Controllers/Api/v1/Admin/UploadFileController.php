<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadFileController extends Controller
{
   public function uploadFile(Request $request): JsonResponse
   {
      if ($request->hasFile('file')) {
         return response()->json([
            'url' => Storage::disk('public')->put('file', $request->file('file'))
         ]);
      }

      return response()->json(['error' => 'File not uploaded'], 400);
   }
}
