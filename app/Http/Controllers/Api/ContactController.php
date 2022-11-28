<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.verify');
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required',
            'phone'        => 'required|numeric|digits_between:10,14',
        ],[
            'name.required'         => 'Nama kontak tidak boleh kosong!',
            'phone.required'        => 'Nomer telepon tidak boleh kosong!',
            'phone.numeric'         => 'Nomer telepon yang dimasukkan bukan angka!',
            'phone.digits_between'  => 'Nomer yang dimasukkan kurang dari 10 angka!',
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data'    => $validator->errors(),
                'message' => 'Gagal menambahkan kontak!',
                'code'    => 422
            ], 422);
        }

        $cekKontak = Contact::where('users_id', $request->user()->id_user)->where('phone', $request->phone)->first();
        
        if ($cekKontak) {
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Nomer telepon sudah ada di daftar kontak!',
                'code'    => 422
            ], 422);
        }

        $contact            = new Contact();
        $contact->name      = $request->name;
        $contact->phone     = $request->phone;
        $contact->users_id  = $request->user()->id_user;
        $contact->save();

        if($contact) {
            //return response JSON user password is updated
            return response()->json([
                'success' => true,
                'data'    => [
                    'contact' => $contact
                ],
                'message' => 'Berhasil menambahkan kontak!',
                'code'    => 200
            ], 200);
        }

        //return JSON process insert failed 
        return response()->json([
            'success' => false,
            'user'    => '',
            'message' => 'Penambahan kontak gagal dilakukan!',
            'code'    => 409
        ], 409);
    }

    public function show(Request $request)
    {
        $contacts = Contact::where('users_id', $request->user()->id_user)->orderBy('name', 'ASC')->get();

        if($contacts) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'contact' => $contacts
                ],
                'message' => 'Berhasil mendapatkan list kontak yang tersimpan!',
                'code'    => 200
            ], 200);
        }

        return response()->json([
            'success' => false,
            'user'    => '',
            'message' => 'Gagal mendapatkan list kontak yang tersimpan!',
            'code'    => 409
        ], 409);
    }

    public function searchContact(Request $request, $find)
    {
        $contact = Contact::where('users_id', $request->user()->id_user)
                                ->where(function ($query) use ($find) {
                                    $query->where('name', 'LIKE', "%{$find}%")
                                    ->orWhere('phone', 'LIKE', "%{$find}%");
                                }
                            )->orderBy('name', 'ASC')->get();

        if($contact) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'contact' => $contact
                ],
                'message' => 'Berhasil mendapatkan kontak yang tersimpan!',
                'code'    => 200
            ], 200);
        }

        return response()->json([
            'success' => false,
            'user'    => '',
            'message' => 'Tidak ada kontak dengan nama atau nomer telepon tersimpan!',
            'code'    => 409
        ], 409);
    }

    public function editContact(Request $request, $find)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required',
            'phone'        => 'required|numeric|digits_between:10,14',
        ],[
            'name.required'         => 'Nama kontak tidak boleh kosong!',
            'phone.required'        => 'Nomer telepon tidak boleh kosong!',
            'phone.numeric'         => 'Nomer telepon yang dimasukkan bukan angka!',
            'phone.digits_between'  => 'Nomer yang dimasukkan kurang dari 10 angka!',
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data'    => $validator->errors(),
                'message' => 'Gagal menambahkan kontak!',
                'code'    => 422
            ], 422);
        }

        $contact = Contact::where('users_id', $request->user()->id_user)->where('phone', $find)->first();
        if($contact) {
            $contact->name = $request->name;
            $contact->phone = $request->phone;
            $contact->save();

            if($contact) {
                return response()->json([
                    'success' => false,
                    'data'    => [
                        'contact' => $contact
                    ],
                    'message' => 'Berhasil mengubah data kontak!',
                    'code'    => 200
                ], 200);
            }
            
            return response()->json([
                'success' => false,
                'user'    => '',
                'message' => 'Gagal mengubah data kontak!',
                'code'    => 406
            ], 406);
        }

        return response()->json([
            'success' => false,
            'user'    => '',
            'message' => 'Kontak tidak tersedia!',
            'code'    => 409
        ], 409);
    }

    public function deleteContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone'        => 'required|numeric|digits_between:10,14',
        ],[
            'phone.required'        => 'Nomer telepon tidak boleh kosong!',
            'phone.numeric'         => 'Nomer telepon yang dimasukkan bukan angka!',
            'phone.digits_between'  => 'Nomer yang dimasukkan kurang dari 10 angka!',
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data'    => $validator->errors(),
                'message' => 'Gagal menghapus kontak!',
                'code'    => 422
            ], 422);
        }

        $contact = Contact::where('users_id', $request->user()->id_user)->where('phone', $request->phone)->first();
        
        //if validation fails
        if(!$contact) {
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Kontak tidak tersedia!',
                'code'    => 422
            ], 422);
        }

        $contactDelete = $contact->delete();

        if($contactDelete) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'service'      => $contact
                ],
                'message' => 'Kontak berhasil dihapus!',
                'code'    => 200
            ], 200);
        }
        
        return response()->json([
            'success' => false,
            'data'    => '',
            'message' => 'Kontak tidak berhasil dihapus!',
            'code'    => 406
        ], 406);
    }
}
