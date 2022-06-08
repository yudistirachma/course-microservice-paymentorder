<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->input('user_id');
        $order = Order::query();

        $order->when($userId, function($query) use ($userId){
            $query->where('user_id', $userId);
        });
        

        return response()->json([
            'status' => true,
            'data' => $order->get()
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->input('user');
        $course = $request->input('course');

        $order = new Order();
        $order->user_id = $user['id'];
        $order->course_id = $course['id'];
        $order->save();

        $transactionDetails = [
            'order_id' => $order->id.'-'. Str::random(5),
            'gross_amount' => $course['price']
        ];

        $itemDetails = [
            [
                'id' => $course['id'],
                'price' => $course['price'],
                'quantity' => 1,
                'name' => $course['name'],
                'brand' => 'yudistirachma',
                'category' => 'Online Course'
            ]
        ];

        $customerDetails = [
            'first_name' => $user['name'],
            'email' => $user['email']
        ];

        $midtransParams = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails
        ];
        

        $midtransSnapUrl = $this->getMidtransSnapUrl($midtransParams);

        $order->snap_url = $midtransSnapUrl;

        $order->metadata = [
            'course_id' => $course['id'],
            'course_price' => $course['price'],
            'course_name' => $course['name'],
            'course_thumbnail' => $course['thumbnail'],
            'course_level' => $course['level']
        ];

        $order->save();

        return response()->json([
            'status' => true,
            'data' => $order
        ]);
    }

    private function getMidtransSnapUrl($params)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = (bool)env('MIDTRANS_PRODUCTION');
        \Midtrans\Config::$isSanitized = (bool)env('MIDTRANS_SANITIZED');
        \Midtrans\Config::$is3ds = (bool)env('MIDTRANS_3DS');

        $snapUrl = \Midtrans\Snap::getSnapUrl($params);

        return $snapUrl;
    }
}
