<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                    'success' => false,
                    'data'    => '',
                    'message' => 'Token yang anda masukkan tidak sah!',
                    'code'    => 409
                ], 409);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json([
                    'success' => false,
                    'data'    => '',
                    'message' => 'Token yang anda masukkan sudah kadaluarsa!',
                    'code'    => 409
                ], 409);
            }else{
                return response()->json([
                    'success' => false,
                    'data'    => '',
                    'message' => 'Token yang anda masukkan tidak ditemukan!',
                    'code'    => 409
                ], 409);
            }
        }
        return $next($request);
    }
}
