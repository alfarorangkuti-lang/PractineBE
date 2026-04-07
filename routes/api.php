<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\StockParentController;
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
    $frontend = env('FRONTEND_URL');
    $pageVerif = '/auth/verification';

    // 🔐 validasi hash email
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid verification link');
    }

    // ✅ kalau sudah verified
    if ($user->hasVerifiedEmail()) {
        $page = '/auth/login';
        return redirect($frontend . $page);
    }

    // ✅ set verified
    $user->markEmailAsVerified();

    event(new Verified($user));

    return redirect($frontend . $pageVerif);
})->middleware(['signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json([
        'message' => 'Verification link sent!'
    ]);
})->middleware(['auth:sanctum', 'throttle:1,1']);


// midtrans
Route::post('/firstSubs', [MidtransController::class, 'firstSubscription'])->middleware(['auth:sanctum']);
Route::post('/subscribe', [MidtransController::class, 'subscribe'])->middleware(['auth:sanctum', 'subsAndRoleChecks:owner']);
Route::post('/midtrans/callback', [MidtransController::class, 'callbackMidtrans']);
Route::post('/testPayment', [MidtransController::class, 'testPayment'])->middleware(['auth:sanctum']);


Route::get('/testMiddleware', function(){
    return response()->json(['message' => 'role benar dan sudah subscribe']);
})->middleware(['auth:sanctum','subsAndRoleChecks:owner']);

Route::middleware(['auth:sanctum', 'subsAndRoleChecks:admin,owner'])->group(function () {
    Route::get('/supplier', [SupplierController::class, 'index']);
    Route::post('/supplier', [SupplierController::class, 'store']);
    Route::put('/supplier/{id}', [SupplierController::class, 'update']);

    Route::post('/custom-field', [CustomFieldController::class, 'store']);
    Route::get('/custom-field', [CustomFieldController::class, 'index']);

    Route::get('/stock-parent', [StockParentController::class, 'index']);
    Route::post('/stock-parent', [StockParentController::class, 'store']);
    Route::put('/stock-parent/{id}', [StockParentController::class, 'update']);
});