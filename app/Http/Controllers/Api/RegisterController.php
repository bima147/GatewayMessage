<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
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
            'phone'     => 'required|digits_between:10,14|unique:users',
            'password'  => 'required|min:8|confirmed'
        ],[
            'name.required'         => 'Nama tidak boleh kosong!',
            'email.required'        => 'Email tidak boleh kosong!',
            'email.email'           => 'Data email yang anda masukkan bukanlah email!',
            'email.unique'          => 'Email sudah terdaftar!',
            'phone.required'        => 'No Telepon tidak boleh kosong!',
            'phone.digits_between'  => 'No Telepon minimal 10 dan maksimal 13 angka!',
            'phone.unique'          => 'No Telepon sudah terdaftar!',
            'password.required'     => 'Password tidak boleh kosong!',
            'password.min'          => 'Password minimal 8 karakter!',
            'password.confirmed'    => 'Password tidak sesua!',
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

        $ulang = true;
        $cek = null;
        $cek1 = null;
        
        while($ulang) {
            $cek = Str::random(15);;
            $cekUser = User::where('user_key', $cek)->first();
            if(!$cekUser) {
                $ulang = false;
            }
            $cek1 = Str::random(30);;
            $cekUser = User::where('pass_key', $cek1)->first();
            if($cekUser) {
                $ulang = true;
            }
        }

        //create user
        $user = User::create([
            'name'      => $request->name,
            'level'     => 'user',
            'balance'   => 0,
            'email'     => $request->email,
            'phone'     => $request->phone,
            // 'password'  => bcrypt($request->password)
            'password'  => Hash::make($request->password),
            'user_key'  => $cek,
            'pass_key'  => $cek1
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