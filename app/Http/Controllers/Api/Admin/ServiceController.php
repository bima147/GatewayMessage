<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function add(Request $request)
    {
        if($request->user()->level == "admin") {
            $validator = Validator::make($request->all(), [
                'name'         => 'required|unique:services,name',
                'max_char'     => 'required|integer|min:1',
                'price'        => 'required|integer|min:1',
                'type'         => 'required',
                'status'       => 'required|in:active,inactive',
            ],[
                'name.required'=> 'Nama layanan tidak boleh kosong!',
                'name.unique'  => 'Nama layanan sudah ada!',
                'max_char.required'  => 'Maksimal huruf di layanan '. $request->name. ' tidak boleh kosong!',
                'max_char.integer'=> 'Maksimal huruf harus integer!',
                'max_char.min' => 'Maksimal huruf di layanan '. $request->name. ' tidak boleh kurang dari 1!',
                'price.required'  => 'Harga di layanan '. $request->name. ' tidak boleh kosong!',
                'price.integer'=> 'Harga harus integer!',
                'price.min'  => 'Harga di layanan '. $request->name. ' tidak boleh kurang dari 1!',
                'type.required'  => 'Tipe di layanan '. $request->name. ' tidak boleh kosong!',
                'status.required'  => 'Status di layanan '. $request->name. ' tidak boleh kosong!',
                'status.in'  => 'Status di layanan '. $request->name. ' tidak boleh selain "active" dan "inactive"!',
            ]);
    
            //if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'data'    => $validator->errors(),
                    'message' => 'Gagal menambahkan layanan!',
                    'code'    => 422
                ], 422);
            }
    
            $service = Service::create([
                'name'      => $request->name,
                'max_char'  => $request->max_char,
                'price'     => $request->price,
                'type'      => $request->type,
                'status'    => $request->status,
                'users_id'  => $request->user()->id_user
            ]);
            
            if($service) {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'service'      => $service
                    ],
                    'message' => 'Layanan berhasil ditambahkan!',
                    'code'    => 201
                ], 201);
            }
            
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Layanan gagal ditambahkan!',
                'code'    => 406
            ], 406);
        } else {
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Anda bukan admin!',
                'code'    => 422
            ], 422);
        }
    }

    public function getServices(Request $request)
    {
        if($request->user()->level == "admin") {
            $service = Service::orderBy('id_service', 'asc')->get();
        
            if($service) {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'service'      => $service
                    ],
                    'message' => 'Berhasil mendapatkan data layanan!',
                    'code'    => 201
                ], 201);
            }
            
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Tidak ada data layanan, silahkan tambahkan terlebih dahulu!',
                'code'    => 422
            ], 422);
        } else {
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Anda bukan admin!',
                'code'    => 422
            ], 422);
        }
    }

    public function getServicesByName(Request $request, $name)
    {
        if($request->user()->level == "admin") {
            $service = Service::where('name', $name)->first();
            
            if($service) {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'service'      => $service
                    ],
                    'message' => 'Layanan berhasil ditambahkan!',
                    'code'    => 201
                ], 201);
            }
            
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Tidak ada data layanan sesuai dengan nama yang dimasukkan, silahkan tambahkan terlebih dahulu!',
                'code'    => 406
            ], 406);
        } else {
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Anda bukan admin!',
                'code'    => 422
            ], 422);
        }
    }

    public function edit(Request $request, $name)
    {
        if($request->user()->level == "admin") {
            if($request->name == $name) {
                $validator = Validator::make($request->all(), [
                    'name'         => 'required',
                    'max_char'     => 'required|integer|min:1',
                    'price'        => 'required|integer|min:1',
                    'type'         => 'required',
                    'status'       => 'required|in:active,inactive',
                ]);
            } else {
                $validator = Validator::make($request->all(), [
                    'name'         => 'required|unique:services,name',
                    'max_char'     => 'required|integer|min:1',
                    'price'        => 'required|integer|min:1',
                    'type'         => 'required',
                    'status'       => 'required|in:active,inactive',
                ]);
            }
    
            //if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'data'    => $validator->errors(),
                    'message' => 'Gagal menghapus layanan!',
                    'code'    => 422
                ], 422);
            }
    
            $service = Service::where('name', $name)->first();
            
            if($service == null) {
                return response()->json([
                    'success' => false,
                    'data'    => '',
                    'message' => 'Nama layanan tidak ada!',
                    'code'    => 422
                ], 422);
            }
    
            $update = $service->update([
                'name'      => $request->name,
                'max_char'  => $request->max_char,
                'price'     => $request->price,
                'type'      => $request->type,
                'status'    => $request->status,
                'users_id'  => $request->user()->id_user
            ]);
            
            if($update) {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'service'      => $service
                    ],
                    'message' => 'Layanan berhasil diubah!',
                    'code'    => 201
                ], 201);
            }
            
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Layanan tidak berhasil diubah!',
                'code'    => 406
            ], 406);
        } else {
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Anda bukan admin!',
                'code'    => 422
            ], 422);
        }
    }

    public function delete(Request $request)
    {
        if($request->user()->level == "admin") {
            $validator = Validator::make($request->all(), [
                'name'         => 'required',
            ]);
    
            //if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'data'    => $validator->errors(),
                    'message' => 'Gagal menghapus layanan!',
                    'code'    => 422
                ], 422);
            }
    
            $service = Service::where('name', $request->name)->first();
            
            if($service == null) {
                return response()->json([
                    'success' => false,
                    'data'    => '',
                    'message' => 'Nama layanan tidak ada!',
                    'code'    => 422
                ], 422);
            }
    
            $serviceDelete = $service->delete();
            
            if($serviceDelete) {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'service'      => $service
                    ],
                    'message' => 'Layanan berhasil dihapus!',
                    'code'    => 201
                ], 201);
            }
            
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Layanan tidak berhasil dihapus!',
                'code'    => 406
            ], 406);
        } else {
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Anda bukan admin!',
                'code'    => 422
            ], 422);
        }
    }
}
