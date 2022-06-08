<?php

namespace App\Http\Controllers;

use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentLogController extends Controller
{
    public function index()
    {
        $paymentLogs = PaymentLog::all();

        return response()->json([
            'status' => true,
            'data' => $paymentLogs,
        ]);
    }

    public function show($paymentLog)
    {
        $paymentLog = PaymentLog::find($paymentLog);

        if (!$paymentLog) {
            return response()->json([
                'status' => false,
                'message' => 'PaymentLog not found',
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $paymentLog,
        ]);
    }

    Public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'status' => 'required',
            'payment_type' => 'required',
            'order_id' => 'required|numeric|exists:orders,id',
            'raw_response' => 'nullable',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validated->errors()->all(),
            ]);
        }

        $paymentLog = PaymentLog::create($request->all());

        return response()->json([
            'status' => true,
            'data' => $paymentLog,
        ]);
    }
}
