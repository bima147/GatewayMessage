<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

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
            $user = User::where('id', $request->user()->id)->update(['password'  => Hash::make($request->newPassword)]);
            
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
}
