<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => [
                $request->user()
            ],
            'message' => "Profile",
            'code'    => 200
        ]);
    }

    public function changePassword(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'oldPassword'      => 'required',
            'newPassword'      => 'required|min:8|confirmed',
        ],[
            'oldPassword.required'      => 'Password lama tidak boleh kosong!',
            'newPassword.required'      => 'Password lama tidak boleh kosong!',
            'newPassword.confirmed'     => 'Password baru dengan konfirmasi password tidak sesuai!',
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data'    => $validator->errors(),
                'message' => 'Gagal mengubah kata sandi!',
                'code'    => 422
            ], 422);
        }

        if(password_verify($request->oldPassword, $request->user()->password)) {
            $user = User::where('id_user', $request->user()->id_user)->update(['password'  => Hash::make($request->newPassword)]);
            
            //return JSON process update failed 
            if($user) {
                //return response JSON user password is updated
                return response()->json([
                    'success' => true,
                    'data'    => $request->user(),
                    'message' => 'Password berhasil diubah!',
                    'code'    => 200
                ], 200);
            }
            return response()->json([
                'success' => false,
                'user'    => '',
                'message' => 'Password gagal diubah!',
                'code'    => 409
            ], 409);
        }

        return response()->json([
            'success' => false,
            'user'    => '',
            'message' => 'Password yang anda masukkan salah!',
            'code'    => 406
        ], 406);
    }

    public function changeProfile(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'phone'     => 'required|numeric|digits_between:10,14',
        ],[
            'name.reqired'              => 'Nama tidak boleh kosong!',
            'phone.required'            => 'Nomer telepon tidak boleh kosong!',
            'phone.numeric'             => 'Nomer telepon yang anda masukkan bukan angka!',
            'phone.digits_between'      => 'Nomer yang dimasukkan kurang dari 10 angka atau lebih dari 13!',
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data'    => $validator->errors(),
                'message' => 'Gagal mengubah profile!',
                'code'    => 422
            ], 422);
        }

        $user = User::where('id_user', $request->user()->id_user)->first();
        $user->name  = $request->name;
        $user->phone = $request->phone;
        $user->save();

        //return JSON process update failed 
        if($user) {
            //return response JSON user password is updated
            return response()->json([
                'success' => true,
                'data'    => $request->user(),
                'message' => 'Password berhasil diubah!',
                'code'    => 200
            ], 200);
        }

        return response()->json([
            'success' => false,
            'user'    => '',
            'message' => 'Profile gagal diubah!',
            'code'    => 409
        ], 409);
    }

    public function logout(Request $request)
    {
        //remove token
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());
        
        if($removeToken) {
            //return response JSON
            return response()->json([
                'success' => true,
                'data'    => '',
                'message' => 'Logout Berhasil!',  
                'code'    => 200
            ]);
        }
    }
}
