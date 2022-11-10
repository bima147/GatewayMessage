<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
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
        ],[
            'name.required'     => 'Nama tidak boleh kosong!',
            'email.required'    => 'Email tidak boleh kosong!',
            'phone.required'    => 'No Telepon tidak boleh kosong!',
            'password.required' => 'Password tidak boleh kosong!',
            'password.confirmed'=> 'Password tidak sesua!',
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
            'balance'   => 0,
            'email'     => $request->email,
            'phone'     => $request->phone,
            // 'password'  => bcrypt($request->password)
            'password'  => Hash::make($request->password)
        ]);

        //return response JSON user is created
        if($user) {
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