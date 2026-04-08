<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return redirect(env('FRONTEND_URL').'/auth/login');
})->name('login');