<?php

namespace App\Http\Controllers\Api\v1\Export;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserExportController extends Controller
{
   public function index(): BinaryFileResponse
   {
      return Excel::download(new UsersExport, 'users.xlsx');
   }
}
