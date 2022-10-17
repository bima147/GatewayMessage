<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'email'     => 'required',
            'password'  => 'required'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => $validator->errors(),
                'code'    => 422
            ], 422);
        }

        //get credentials from request
        $credentials = $request->only('email', 'password');

        //if auth failed
        if(!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Email atau Password Anda salah',
                'code'    => 401
            ], 401);
        }

        //if auth success
        return response()->json([
            'success' => true,
            'data'    => [
                'token' => $token
            ],
            'message' => "Login",
            'code'    => 200
        ]);
    }
}