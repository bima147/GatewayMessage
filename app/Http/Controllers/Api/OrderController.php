<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\UpdateController;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'         => 'required|in:SMS,Whatsapp,Voice',
        ],[
            'type.required'=> 'Service tidak boleh kosong!',
            'type.in'      => 'Service hanya tersedia SMS, Whatsapp, Voice',
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

        $service = Service::where('name', $request->type)->first();
        $datetime = Carbon::now();
        $date = $datetime->format('Y-m-d');
        $time = $datetime->format('H:i:s');
        
        $validator = Validator::make($request->all(), [
            'phone'        => 'required|numeric|digits_between:10,14',
            'send_date'    => 'required|after_or_equal:' . $date,
            'message'      => 'required|max:' . $service->max_char,
            'send_time'    => 'required',
        ],[
            'phone.required'            => 'Nomer telepon tidak boleh kosong!',
            'phone.numeric'             => 'Nomer telepon yang anda masukkan bukan angka!',
            'phone.digits_between'      => 'Nomer yang dimasukkan kurang dari 10 angka atau lebih dari 13!',
            'send_date.required'        => 'Tanggal pengiriman tidak boleh kosong!',
            'send_date.after_or_equal'  => 'Tanggal yang dimasukkan tidak dapat sebelum tanggal ' . $date,
            'message.required'          => 'Pesan tidak boleh kosong!',
            'message.max'               => 'Jumlah huruf yang dimasukkan melebihi batas maksimal!',
            'send_time.required'        => 'Waktu pengiriman tidak boleh kosong!',
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

        $layanan = 'send' . $service->name . 'Image';
        if($request->image_link == null) {
            $request->image_link = '';
            $layanan = 'send' . $service->name;
        }
        if($request->caption == null) {
            $request->caption = '';
        }
        $price = $service->price;
        
        if($request->user()->balance < $price) {
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Saldo anda tidak cukup!',
                'code'    => 406
            ], 406);
        }
        
        if($request->send_date == $date && $request->send_time <= $time) {
            $order = UpdateController::$layanan($request->phone, $request->message, $request->image_link, $request->caption);
            
            if($order['messageId'] == null) {
                return response()->json([
                    'success' => false,
                    'data'    => '',
                    'message' => $order['text'],
                    'code'    => 409
                ], 409);
            }

            $status = UpdateController::getStatus($service->type, $order['messageId']);
            
            //return JSON process update failed 
            if($order) {
                $users = User::find($request->user()->id_user)->first();
                $users->balance = $request->user()->balance - $price;
                $users->save();

                $createOrder = new Order();
                $createOrder->phone         = $request->phone;
                $createOrder->message       = $request->message;
                $createOrder->send_date     = $request->send_date;
                $createOrder->send_time     = $request->send_time;
                if($request->image_link != null) {
                    $createOrder->image     = $request->image_link;
                }
                if($request->caption != null) {
                    $createOrder->caption       = $request->caption;
                }
                $createOrder->price         = $price;
                if($status["msg-status"] != null) {
                    $createOrder->status        = $status["msg-status"];
                } else {
                    $createOrder->status        = 'No Status';
                }
                $createOrder->users_id      = $request->user()->id_user;
                $createOrder->type          = $service->id_service;
                $createOrder->message_id    = $order['messageId'];
                $createOrder->save();

                if($createOrder) {
                    //return response JSON user password is updated
                    return response()->json([
                        'success' => true,
                        'data'    => [
                            'order' => $createOrder
                        ],
                        'message' => 'Berhasil membuat pesanan!',
                        'code'    => 200
                    ], 200);
                }
            }
        } else {
            $users = User::find($request->user()->id_user)->first();
            $users->balance = $request->user()->balance - $price;
            $users->save();
            
            $createOrder = new Order();
            $createOrder->phone         = $request->phone;
            $createOrder->message       = $request->message;
            $createOrder->send_date     = $request->send_date;
            $createOrder->send_time     = $request->send_time;
            if($request->image_link != null) {
                $createOrder->image     = $request->image_link;
            }
            if($request->caption != null) {
                $createOrder->caption       = $request->caption;
            }
            $createOrder->price         = $price;
            $createOrder->status        = 'Waiting';
            $createOrder->users_id      = $request->user()->id_user;
            $createOrder->type          = $service->id_service;
            $createOrder->save();
    
            if($createOrder) {
                //return response JSON when success
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'order' => $createOrder
                    ],
                    'message' => 'Berhasil membuat pesanan!',
                    'code'    => 200
                ], 200);
            }
        }
        //return JSON process insert failed 
        return response()->json([
            'success' => false,
            'user'    => '',
            'message' => 'Pemesanan gagal dilakukan!',
            'code'    => 409
        ], 409);
    }

    public function getOrder(Request $request)
    {
        $order = Order::where('users_id', $request->user()->id_user)->get();
        
        if($order) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'order'      => $order
                ],
                'message' => 'Berhasil mendapatkan data pesanan!',
                'code'    => 201
            ], 201);
        }
        
        return response()->json([
            'success' => false,
            'data'    => '',
            'message' => 'Anda belum mempunyai pesanan, silahkan melakukan pemesanan terlebih dahulu!',
            'code'    => 406
        ], 406);
    }

    public function getOrderByID(Request $request, $find)
    {
        $order = Order::where('id_order', $find)->first();
        
        if($order) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'order'      => $order
                ],
                'message' => 'Berhasil mendapatkan data pesanan!',
                'code'    => 201
            ], 201);
        }
        
        return response()->json([
            'success' => false,
            'data'    => '',
            'message' => 'Order ID tidak ditemukan, silahkan cek kembali!',
            'code'    => 406
        ], 406);
    }

    public function updateOrder(Request $request, $find)
    {
        $validator = Validator::make($request->all(), [
            'type'         => 'required|in:SMS,Whatsapp,Voice',
        ],[
            'type.required'=> 'Service tidak boleh kosong!',
            'type.in'      => 'Service hanya tersedia SMS, Whatsapp, Voice',
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

        $service = Service::where('name', $request->type)->first();
        $datetime = Carbon::now();
        $date = $datetime->format('Y-m-d');
        $time = $datetime->format('H:i:s');
        
        $validator = Validator::make($request->all(), [
            'phone'        => 'required|numeric|digits_between:10,14',
            'send_date'    => 'required|after_or_equal:' . $date,
            'message'      => 'required|max:' . $service->max_char,
            'send_time'    => 'required',
        ],[
            'phone.required'            => 'Nomer telepon tidak boleh kosong!',
            'phone.numeric'             => 'Nomer telepon yang anda masukkan bukan angka!',
            'phone.digits_between'      => 'Nomer yang dimasukkan kurang dari 10 angka atau lebih dari 13!',
            'send_date.required'        => 'Tanggal pengiriman tidak boleh kosong!',
            'send_date.after_or_equal'  => 'Tanggal yang dimasukkan tidak dapat sebelum tanggal ' . $date,
            'message.required'          => 'Pesan tidak boleh kosong!',
            'message.max'               => 'Jumlah huruf yang dimasukkan melebihi batas maksimal!',
            'send_time.required'        => 'Waktu pengiriman tidak boleh kosong!',
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

        $layanan = 'send' . $service->name . 'Image';
        if($request->image_link == null) {
            $request->image_link = '';
            $layanan = 'send' . $service->name;
        }
        if($request->caption == null) {
            $request->caption = '';
        }
        $price = $service->price;

        $updateOrder = Order::where('id_order', $find)->first();

        $users = User::find($request->user()->id_user)->first();
        $users->balance = $request->user()->balance + $updateOrder->price;
        $users->save();
        
        if($request->user()->balance < $price) {
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Saldo anda tidak cukup!',
                'code'    => 406
            ], 406);
        }

        if($request->send_date == $date && $request->send_time <= $time) {
            $order = UpdateController::$layanan($request->phone, $request->message, $request->image_link, $request->caption);
            
            if($order['messageId'] == null) {
                return response()->json([
                    'success' => false,
                    'data'    => '',
                    'message' => $order['text'],
                    'code'    => 409
                ], 409);
            }

            $status = UpdateController::getStatus($service->type, $order['messageId']);
            
            //return JSON process update failed 
            if($order) {
                $users = User::find($request->user()->id_user)->first();
                $users->balance = $request->user()->balance - $price;
                $users->save();
                
                $updateOrder->phone         = $request->phone;
                $updateOrder->message       = $request->message;
                $updateOrder->send_date     = $request->send_date;
                $updateOrder->send_time     = $request->send_time;
                if($request->image_link != null) {
                    $updateOrder->image     = $request->image_link;
                }
                if($request->caption != null) {
                    $updateOrder->caption       = $request->caption;
                }
                $updateOrder->price         = $price;
                if($status["msg-status"] != null) {
                    $updateOrder->status        = $status["msg-status"];
                } else {
                    $updateOrder->status        = 'No Status';
                }
                $updateOrder->type          = $service->id_service;
                $updateOrder->message_id    = $order['messageId'];
                $updateOrder->save();

                if($updateOrder) {
                    //return response JSON user password is updated
                    return response()->json([
                        'success' => true,
                        'data'    => [
                            'order' => $updateOrder
                        ],
                        'message' => 'Berhasil mengubah pesanan!',
                        'code'    => 200
                    ], 200);
                }
            }
        } else {
            $users = User::find($request->user()->id_user)->first();
            $users->balance = $request->user()->balance - $price;
            $users->save();
            
            $updateOrder->phone         = $request->phone;
            $updateOrder->message       = $request->message;
            $updateOrder->send_date     = $request->send_date;
            $updateOrder->send_time     = $request->send_time;
            if($request->image_link != null) {
                $updateOrder->image     = $request->image_link;
            }
            if($request->caption != null) {
                $updateOrder->caption       = $request->caption;
            }
            $updateOrder->price         = $price;
            $updateOrder->type          = $service->id_service;
            $updateOrder->save();
    
            if($updateOrder) {
                //return response JSON when success
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'order' => $updateOrder
                    ],
                    'message' => 'Berhasil mengubah pesanan!',
                    'code'    => 200
                ], 200);
            }
        }

        //return JSON process insert failed 
        return response()->json([
            'success' => false,
            'user'    => '',
            'message' => 'Pembaruan data gagal dilakukan!',
            'code'    => 409
        ], 409);
    }

    public function cancelOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id'        => 'required|numeric',
        ],[
            'order_id.required'        => 'Order ID tidak boleh kosong!',
            'order_id.numeric'         => 'Order ID yang dimasukkan bukan angka!',
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data'    => $validator->errors(),
                'message' => 'Gagal membatalkan Pesanan!',
                'code'    => 422
            ], 422);
        }

        $orderCanceled = Order::where('users_id', $request->user()->id_user)->where('id_order', $request->order_id)->first();
        
        //if validation fails
        if(!$orderCanceled) {
            return response()->json([
                'success' => false,
                'data'    => '',
                'message' => 'Order ID tidak tersedia!',
                'code'    => 422
            ], 422);
        }

        $orderCanceled->status = 'Canceled';
        $orderCanceled->save();

        if($orderCanceled) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'order'      => $orderCanceled
                ],
                'message' => 'Pesanan berhasil dibatalkan!',
                'code'    => 200
            ], 200);
        }
        
        return response()->json([
            'success' => false,
            'data'    => '',
            'message' => 'Pesanan tidak berhasil dibatalkan!',
            'code'    => 406
        ], 406);
    }
}
