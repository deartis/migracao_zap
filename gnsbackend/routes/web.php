<?php

use App\Http\Controllers\InportListController;
use App\Http\Controllers\SingleContactController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Requests\SendMessageRequest;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    Route::get('/', function () {
        return view('pages.home');
    })->name('home');

    // Rota pública para verificação de saúde (opcional)
    Route::get('/whatsapp/health', function (WhatsAppService $service) {
        return $service->healthCheck()->json();
    });

    /*Route::post('/whatsapp-send', function (SendMessageRequest $request, WhatsAppService $whatsapp){
        $message = $request->input('message');
        $number = $request->input('number');
        $media = $request->input('media');

        $token = env('WHATSAPP_API_TOKEN');

        $response = $whatsapp->sendMessage($number, $message, $media, $token);

        return response()->json($response);
    })->name('whatsapp.send');*/

    /**
     * ===========================================================
     * | Rota para Saber o status Atual
     * ============================================================
     */
    Route::get('/whatsapp-status', function (WhatsAppService $whatsapp) {
        $token = env('WHATSAPP_API_TOKEN');

        $response = $whatsapp->checkConnection($token);

        return response()->json($response->json());
    })->name('whatsapp.status');

    /**
     * ===========================================================
     * | Rota para Uniciar o WhatsApp
     * ===========================================================
     */
    Route::get('/whatsapp-connect', function (WhatsAppService $whatsapp) {
        $token = env('WHATSAPP_API_TOKEN');

        $response = $whatsapp->startWhatsApp($token);
        return response()->json($response->json());
    })->name('whatsapp.connect');

    /**
     * ==================================
     * Rotas de páginas                 =
     * ==================================
     */
    //Rotas comuns
    Route::get('/page/single-contact', [SingleContactController::class, 'index'])->name('page.single.contact');
    Route::post('/whatsapp-send', [WhatsAppController::class, 'sendMessage'])->name('whatsapp.send');

    Route::get('/page/from-sheet',[InportListController::class, 'index'])->name('page.from.sheet');
    Route::post('/page/from-sheet',[InportListController::class, 'uploadSheet'])->name('upload.sheet');


    //Rotas de admins
    Route::get('/adm/page/user', [UserController::class, 'index'])->name('adm.user');
    Route::get('/adm/page/register-user', [UserController::class, 'registerUser'])->name('adm.register.user');
    Route::post('/adm/page/register-user', [UserController::class, 'store'])->name('add.user');

    /**
     * Route::get('/start', [WhatsAppController::class, 'start']);
     * Route::get('/status', [WhatsAppController::class, 'checkStatus']);
     * Route::post('/send', [WhatsAppController::class, 'sendMessage']);
     * Route::delete('/session', [WhatsAppController::class, 'deleteSession']);
     *
     *      Route::get('/test-whatsapp', function (WhatsAppService $whatsapp) {
     *     $response = $whatsapp->startWhatsApp();
     *     $data = $response->json();
     *
     *     // Se tiver QR code, exiba-o em uma página
     *     if (isset($data['qrCode'])) {
     *         return view('whatsapp-test', ['qrCode' => $data['qrCode']]);
     *     }
     *
     *     // Se já estiver conectado ou houver outro status
     *     return response()->json($data);
     * });
     */
});
