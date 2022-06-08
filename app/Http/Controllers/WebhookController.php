<?php

namespace App\Http\Controllers;

use App\Models\{Order, PaymentLog};
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function midtransHandler(Request $request)
    {
        $data = $request->all();
        $signatureKey = $data['signature_key'];
        $orderId = $data['order_id'];
        $statusCode = $data['status_code'];
        $grossAmount = $data['gross_amount'];
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $transactionStatus = $data['transaction_status'];
        $type = $data['payment_type'];
        $fraudStatus = $data['fraud_status'];

        $mySignatureKey = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if ($signatureKey !== $mySignatureKey) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid signature key',
                'data' => ['signature_key' => $signatureKey, 'my_signature_key' => $mySignatureKey]
            ], 400);
        }

        $realOrderId = explode('-', $orderId)[0];
        $order = Order::find($realOrderId);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        if ($order->status === 'success') {
            return response()->json([
                'status' => false,
                'message' => 'Order already success'
            ], 405);
        }

        if ($transactionStatus == 'capture'){
            if ($fraudStatus == 'challenge'){
                $order->status = 'challenge';
            } else if ($fraudStatus == 'accept'){
                $order->status = 'success';
            }
        } else if ($transactionStatus == 'settlement'){
            $order->status = 'success';
        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire'){
            $order->status = 'failure';
        } else if ($transactionStatus == 'pending'){
          $order->status = 'pending';
        }

        $logData = [
            'status' => $transactionStatus,
            'raw_response' => json_encode($data),
            'order_id' => $realOrderId,
            'payment_type' => $type,
        ];

        PaymentLog::create($logData);
        $order->save();

        if ($order->status !== 'success') {
            return response()->json([
                'status' => false,
                'message' => 'Order not success, please order again'
            ], 405);
        }
        
        createPremiumAccess([
            'user_id' => $order->user_id,
            'course_id' => $order->course_id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Success'
        ]);
    }
}
