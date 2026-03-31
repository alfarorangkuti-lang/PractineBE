<?php

namespace App\Http\Controllers;

use App\Models\PaymentHistory;
use Midtrans\Snap;
use Illuminate\Http\Request;
\Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

class MidtransController extends Controller
{
    public function firstSubscription(Request $request)
    {
        $user = $request->user();

        $newAccount = PaymentHistory::where('user_id', $user->id)->where('status', 'paid')->exists();
        if ($newAccount) {
            return response()->json(['message' => 'bukan akun baru, transaksi tidak dapat dilakukan']);
        }


        $pendingTransaction = PaymentHistory::where('user_id', $user->id)->where('status', 'pending')->latest()->first();

        if ($pendingTransaction) {
            return response()->json(['snap_token' => $pendingTransaction->snap_token]);
        }



        $orderId = 'ORDER-'. $user->id . time();
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
            'user_id' => $user->id,
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

        return response()->json(['message' => 'OK']);
    }
}
