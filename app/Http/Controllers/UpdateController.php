<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function balance()
    {
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://console.zenziva.net/api/balance/?userkey='.env('ZNZV_USERKEY').'&passkey='.env('ZNZV_PASSKEY'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }

    public function updateAllStatus() {
        $orders = Order::where('status', 'SENT')
                        ->orWhere('status', 'SENDING')
                        ->orWhere('status', 'Waiting')
                        ->orWhere('status', 'DELIVERED')
                        ->get();
        if($orders != null) {
            foreach ($orders as $order) {
                if($order->message_id != null) {
                    $service = Service::where('id_service', $order->type)->first();
                    $status = UpdateController::getStatus($service->type, $order->message_id);

                    $update = Order::where('id_order', $order->id_order)->first();
                    if($status["msg-status"] != NULL) {
                        $update->status = $status["msg-status"];
                    } else {
                        $update->status = 'Waiting';
                    }
                    $update->save();
                }
            }
            \Log::info("Berhasil memperbarui status!");
        }
        \Log::info("Gagal memperbarui status!");
    }

    public function sendOrder() {
        $datetime = Carbon::now();
        $date = $datetime->format('Y-m-d');
        $orders = Order::Where('status', 'Waiting')->where('send_date', $date)->get();

        if($orders != null) {
            foreach ($orders as $order) {
                $service = Service::where('id_service', $order->type)->first();
                $time = $datetime->format('H:i:s');

                $layanan = 'send' . $service->name . 'Image';
                if($order->image_link == null) {
                    $order->image_link = '';
                    $layanan = 'send' . $service->name;
                }
                if($order->caption == null) {
                    $order->caption = '';
                }
    
                if($order->send_time <= $time) {
                    $send = UpdateController::$layanan($order->phone, $order->message, $order->image_link, $order->caption);
                    
                    if($send['messageId'] == null) {
                        return response()->json([
                            'success' => false,
                            'data'    => '',
                            'message' => $send['text'],
                            'code'    => 409
                        ], 409);
                    }
        
                    $status = UpdateController::getStatus($service->type, $send['messageId']);
                    
                    //return JSON process update failed 
                    if($send) {
                        $createOrder = Order::where('id_order', $order->id_order)->first();
                        if($status["msg-status"] != null) {
                            $createOrder->status        = $status["msg-status"];
                        } else {
                            $createOrder->status        = 'No Status';
                        }
                        $createOrder->message_id    = $send['messageId'];
                        $createOrder->save();
                    }
                }
                \Log::info("Berhasil mengirim pesanna");
            }
        }
    }

    public static function getStatus($service, $messageId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://console.zenziva.net/' . $service . '/api/report?userkey=' . env('ZNZV_USERKEY') . '&passkey=' . env('ZNZV_PASSKEY') . '&messageId=' . $messageId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => array('userkey' => env('ZNZV_USERKEY'),'passkey' => env('ZNZV_PASSKEY'),'messageId' => $messageId),
        ));

        $response = json_decode(curl_exec($curl), true);

        curl_close($curl);
        return $response;
    }

    public static function sendSMS($phone, $message, $image_link, $caption)
    {
        $url = 'https://console.zenziva.net/reguler/api/sendsms/';
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array(
            'userkey' => env('ZNZV_USERKEY'),
            'passkey' => env('ZNZV_PASSKEY'),
            'to' => $phone,
            'message' => $message
        ));
        $results = json_decode(curl_exec($curlHandle), true);
        curl_close($curlHandle);

        return $results;
    }

    public static function sendWhatsapp($phone, $message, $image_link, $caption)
    {
        $url = 'https://console.zenziva.net/wareguler/api/sendWA/';
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array(
            'userkey' => env('ZNZV_USERKEY'),
            'passkey' => env('ZNZV_PASSKEY'),
            'to' => $phone,
            'message' => $message
        ));
        $results = json_decode(curl_exec($curlHandle), true);
        curl_close($curlHandle);

        return $results;
    }

    public static function sendWhatsappImage($phone, $message, $image_link, $caption)
    {
        $url = 'https://console.zenziva.net/wareguler/api/sendWAFile/';
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array(
            'userkey' => env('ZNZV_USERKEY'),
            'passkey' => env('ZNZV_PASSKEY'),
            'to' => $phone,
            'message' => $message,
            'link' => $image_link,
            'caption' => $caption
        ));
        $results = json_decode(curl_exec($curlHandle), true);
        curl_close($curlHandle);

        return $results;
    }

    public static function sendVoice($phone, $message, $image_link, $caption)
    {
        $url = 'https://console.zenziva.net/voice/api/sendvoice/';
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array(
            'userkey' => env('ZNZV_USERKEY'),
            'passkey' => env('ZNZV_PASSKEY'),
            'to' => $phone,
            'message' => $message
        ));
        $results = json_decode(curl_exec($curlHandle), true);
        curl_close($curlHandle);

        return $results;
    }
}
