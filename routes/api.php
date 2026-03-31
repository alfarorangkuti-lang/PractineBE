<?php

use App\Http\Controllers\AuthController;
use Illuminate\Auth\Events\Verified;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\MidtransController;

// basic auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
Route::post('/logout', [AuthController::class, 'logout']);
});






// Middleware yang cuma perlu auth:sanctum
// user
Route::middleware(['auth:sanctum'])->get('/user', [AuthController::class, 'user']);

// email verif
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    // 🔐 validasi hash email
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid verification link');
    }

    // ✅ kalau sudah verified
    if ($user->hasVerifiedEmail()) {
        return redirect('http://localhost:3000/auth/login');
    }

    // ✅ set verified
    $user->markEmailAsVerified();

    event(new Verified($user));

    return redirect('http://localhost:3000/auth/verification');
})->middleware(['signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json([
        'message' => 'Verification link sent!'
    ]);
})->middleware(['auth:sanctum', 'throttle:1,1']);


// first subs
Route::post('/firstSubs', [MidtransController::class, 'firstSubscription'])->middleware(['auth:sanctum']);
Route::post('/midtrans/callback', [MidtransController::class, 'callbackMidtrans']);