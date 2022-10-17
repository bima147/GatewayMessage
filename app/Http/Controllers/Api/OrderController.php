<?php

namespace App\Http\Controllers\Api;

use App\Models\Users;
use App\Models\Order;
use App\Models\Balance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function order(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'phone'        => 'required|min:10',
            'message'      => 'required',
            'type'         => 'required',
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data'    => $validator->errors(),
                'message' => 'Gagal melakukan pemesanan!',
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
