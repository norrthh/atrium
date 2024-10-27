<?php

namespace App\Http\Controllers\Api\v1\Authentication;

use App\Http\Controllers\Controller;
use App\Models\AuthLog;
use App\Services\User\UserAuthenticationServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function auth(Request $request, UserAuthenticationServices $services): JsonResponse
    {
        Storage::put('test.json', print_r($request->all(), true));
        return response()->json($services->authenticate($request->all()));
    }
}
