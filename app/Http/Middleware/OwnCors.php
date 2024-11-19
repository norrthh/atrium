<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OwnCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       Log::info('test', (array)print_r($request->all(), 1));
       return $next($request)
          ->header('Access-Control-Allow-Origin', '*')
          ->header('Access-Control-Allow-Methods', '*')
          ->header('Access-Control-Allow-Credentials', true)
          ->header('Access-Control-Allow-Headers', 'X-Requested-With,Content-Type,X-Token-Auth,Authorization')
          ->header('Accept', 'application/json');
    }
}
