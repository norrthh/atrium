<?php

namespace App\Http\Controllers\Api\v1\Coupon;

use App\Http\Controllers\Controller;
use App\Services\Coupon\CouponInsertServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function insert(Request $request, CouponInsertServices $services): JsonResponse
    {
        $request->validate([
            'coupon' => ['required']
        ]);

        return response()->json($services->insert($request->get('coupon')));
    }
}
