<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mailing;
use Illuminate\Http\Request;

class MailingController extends Controller
{
   public function store(Request $request)
   {
      $request->validate([
         'social' => ['required', 'int'],
         'text' => ['required', 'string'],
         'link' => ['required', 'string'],
      ]);

      $data = $request->all();
      $data['text'] = str_replace('{link}', $request->get('link'), $request->get('text'));
      $data['status'] = 0;
      Mailing::query()->create($data);

      return response()->json(['success' => true]);
   }
}
