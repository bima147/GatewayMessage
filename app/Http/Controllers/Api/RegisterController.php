<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Balance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
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
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'phone'     => 'required|min:10|unique:users',
            'password'  => 'required|min:8|confirmed'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data'    => $validator->errors(),
                'message' => 'Pendaftaran gagal!',
                'code'    => 422
            ], 422);
        }

        //create user
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            // 'password'  => bcrypt($request->password)
            'password'  => Hash::make($request->password)
        ]);

        //create user
        $balance = Balance::create([
            'nominal'   => 0,
            'users_id'  => $user->id,
        ]);

        //return response JSON user is created
        if($user && $balance) {
            return response()->json([
                'success' => true,
                'user'    => $user,
                'message' => 'Pendaftaran berhasil dilakukan!',
                'code'    => 201
            ], 201);
        }

        //return JSON process insert failed 
        return response()->json([
            'success' => false,
            'user'    => '',
            'message' => 'Pendaftaran gagal dilakukan!',
            'code'    => 409
        ], 409);
    }
}