<?php

use App\Http\Controllers\HistoricController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InportListController;
use App\Http\Controllers\MultipleContactMsgController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SingleContactController;
use App\Http\Controllers\ToRespondMsgController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsAppController;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [HomeController::class,'index'])->name('home');

    // Rota pública para verificação de saúde (opcional)
    Route::get('/whatsapp/health', function (WhatsAppService $service) {
        return $service->healthCheck()->json();
    });

    Route::get('/whatsapp-status', function (WhatsAppService $whatsapp) {
        $token = env('WHATSAPP_API_TOKEN');

        $response = $whatsapp->checkConnection($token);

        return response()->json($response->json());
    })->name('whatsapp.status');

    Route::get('/whatsapp-connect', function (WhatsAppService $whatsapp) {
        $token = env('WHATSAPP_API_TOKEN');

        $response = $whatsapp->startWhatsApp($token);
        return response()->json($response->json());
    })->name('whatsapp.connect');


    //Rotas comuns
    Route::get('/page/single-contact', [SingleContactController::class, 'index'])->name('page.single.contact');
    Route::post('/whatsapp-send', [WhatsAppController::class, 'sendMessage'])->name('whatsapp.send');
    Route::post('/whatsapp-send-bulk', [WhatsAppController::class, 'sendBulkMessages'])->name('whatsapp.send.bulk');
    Route::get('/live', [ToRespondMsgController::class, 'index'])->name('page.respond.msg');
    Route::post('/responder', [WhatsAppController::class, 'responder'])->name('whatsapp.responder');
    Route::get('/historic', [HistoricController::class, 'index'])->name('page.historic');

    Route::get('/profile', [ProfileController::class, 'index'])->name('page.profile');

    Route::get('/page/from-sheet',[InportListController::class, 'index'])->name('page.from.sheet');
    Route::post('/page/from-sheet',[InportListController::class, 'uploadSheet'])->name('upload.sheet');

    Route::get('/page/multiple-msg',[MultipleContactMsgController::class, 'index'])->name('page.multi.msg');
    Route::get('/dashboard', [WhatsAppController::class, 'dashboard'])->name('dashboard');


    Route::middleware(['auth', 'role:admin'])->group(function(){
        //Rotas de admins
        Route::get('/adm/page/user', [UserController::class, 'index'])->name('adm.user');
        Route::get('/adm/page/register-user', [UserController::class, 'registerUser'])->name('adm.register.user');
        Route::post('/adm/page/register-user', [UserController::class, 'store'])->name('add.user');

        //CRUDE Users
        Route::resource('users', UserController::class);
    });

});

