<?php

namespace App\Http\Controllers\Api\v1\Export;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserExportController extends Controller
{
   public function index()
   {
      $response = Excel::download(new UsersExport, 'users.xlsx');

      // Установим дополнительные заголовки
      $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
      $response->headers->set('Pragma', 'no-cache');
      $response->headers->set('Expires', '0');

      return $response;
   }
}
