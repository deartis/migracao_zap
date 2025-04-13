<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/whatsapp/send', [WhatsAppController::class, 'sendMessage'])->middleware('auth:api');

Route::middleware('auth:api')->group(function () {
    Route::get('/user-can-send', [UserController::class, 'canSend']);
    Route::get('/get-user', [UserController::class, 'getUser']);
});
