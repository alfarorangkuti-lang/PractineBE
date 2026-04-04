<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use App\Models\PaymentHistory;
use App\Models\Tenants;
use Midtrans\Snap;
use Illuminate\Http\Request;
\Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

class MidtransController extends Controller
{

    public function subscribe(Request $request)
    {
        $user = $request->user();
        $pendingTransaction = PaymentHistory::where('tenant_id', $user->tenant_id)->where('status', 'pending')->latest()->first();
        $orderId = 'ORDER-'. $user->tenant_id . time();
        $monthAmount = $request->monthAmount;
        $prices = [
            1 => 129000,
            3 => 339000,
            6 => 600000,
            12 => 1080000,
        ];
        $payAmount = $prices[$monthAmount] ?? abort(400);


        if ($pendingTransaction) {
            return response()->json(['snap_token' => $pendingTransaction->snap_token]);
        }
        

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $payAmount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        PaymentHistory::create([
            'tenant_id' => $user->tenant_id,
            'month_amount' => $monthAmount,
            'pay_amount' => $payAmount,
            'status' => 'pending',
            'snap_token' => $snapToken,
            'order_id' => $orderId,
        ]);


        return response()->json([
            'snap_token' => $snapToken
        ]);

    }

    public function firstSubscription(Request $request)
    {
        $user = $request->user()->load('tenant');

        if ($user->tenant->expired_at) {
            return response()->json(['message' => 'bukan akun baru, transaksi tidak dapat dilakukan']);
        }


        $pendingTransaction = PaymentHistory::where('tenant_id', $user->tenant_id)->where('status', 'pending')->latest()->first();

        if ($pendingTransaction) {
            return response()->json(['snap_token' => $pendingTransaction->snap_token]);
        }



        $orderId = 'ORDER-'. $user->tenant_id . time();
        $payAmount = 9900;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $payAmount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
        ];
        $snapToken = Snap::getSnapToken($params);

        PaymentHistory::create([
            'tenant_id' => $user->tenant_id,
            'month_amount' => 1,
            'pay_amount' => $payAmount,
            'status' => 'pending',
            'snap_token' => $snapToken,
            'order_id' => $orderId,
        ]);


        return response()->json([
            'snap_token' => $snapToken
        ]);
    }

    public function callbackMidtrans(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $signature = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        if ($signature !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = $request->order_id;
        $transactionStatus = $request->transaction_status;
        $fraudStatus = $request->fraud_status;

        $payment = PaymentHistory::where('order_id', $orderId)->first();
        if (!$payment) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if($transactionStatus == 'capture'){
            if ($fraudStatus == 'challenge') {
                $payment->status = $fraudStatus;
            } else if($fraudStatus == 'accept') {
                $payment->status == 'paid';
            }
        }
        else if ($transactionStatus == 'settlement') {
        $payment->status = 'paid';
        } 
        else if ($transactionStatus == 'pending') {
            $payment->status = 'pending';
        } 
        else if ($transactionStatus == 'deny') {
            $payment->status = 'failed';
        } 
        else if ($transactionStatus == 'expire') {
            $payment->status = 'expired';
        } 
        else if ($transactionStatus == 'cancel') {
            $payment->status = 'cancelled';
        }

        $payment->save();
        
        $tenant = Tenants::where('id', $user->tenant_id)->first();
        $baseDate = $tenant->expired_at && $tenant->expired_at > now() ? $tenant->expired_at : now();
        if ($payment->status === 'paid') {
            $tenant->expired_at = $baseDate->addMonths($payment->month_amount);
        }

        return response()->json(['message' => 'OK']);
    }

    public function testPayment(Request $request)
    {
        $user = $request->user()->load('tenant');

        $payment = PaymentHistory::where('tenant_id', $user->tenant->id)->first();
        if (!$payment) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $payment->status = 'paid';
        $payment->save();
        
        $tenant = Tenants::where('id', $user->tenant->id)->first();
        $baseDate = $tenant->expired_at && $tenant->expired_at > now() ? $tenant->expired_at : now();
        if ($payment->status === 'paid') {
            $tenant->expired_at = $baseDate->addMonths(1);
            $tenant->save();
        }

        return response()->json(['message' => 'OK'],200);
    }
}
