<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Tasks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TasksController extends Controller
{
    public function getTasks(): \Illuminate\Database\Eloquent\Collection
    {
        return Tasks::query()->get();
    }
}
