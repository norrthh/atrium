<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\Coupons\CouponsServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function insert(Request $request, CouponsServices $services): JsonResponse
    {
        $request->validate([
            'coupon' => ['required']
        ]);

        return response()->json([
           'status' => $services->insert($request->get('coupon'))
        ]);
    }
}
