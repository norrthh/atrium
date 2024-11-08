<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\UserAuthenticationServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function auth(Request $request, UserAuthenticationServices $services): JsonResponse
    {
        return response()->json($services->authenticate($request->all()));
    }
}
