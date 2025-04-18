<?php

use App\Http\Controllers\SingleContactController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Requests\SendMessageRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use App\Services\WhatsAppService;

Route::get('/', function () {
    return view('pages.home');
});

Route::middleware('auth:sanctum')->prefix('whatsapp')->group(function () {
    Route::get('/start', [WhatsAppController::class, 'start']);
    Route::get('/status', [WhatsAppController::class, 'checkStatus']);
    Route::post('/send', [WhatsAppController::class, 'sendMessage']);
    Route::delete('/session', [WhatsAppController::class, 'deleteSession']);
});

Route::get('/test-whatsapp', function (WhatsAppService $whatsapp) {
    $response = $whatsapp->startWhatsApp();
    $data = $response->json();

    // Se tiver QR code, exiba-o em uma página
    if (isset($data['qrCode'])) {
        return view('whatsapp-test', ['qrCode' => $data['qrCode']]);
    }

    // Se já estiver conectado ou houver outro status
    return response()->json($data);
});


// Rota pública para verificação de saúde (opcional)
Route::get('/whatsapp/health', function (WhatsAppService $service) {
    return $service->healthCheck()->json();
});

/**
 * ===========================================================
 * | Rota para envio de mensagem única
 *============================================================
 */
Route::post('/whatsapp-send', [WhatsAppController::class, 'sendMessage'])->name('whatsapp.send');

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
 *============================================================
 */
Route::get('/whatsapp-status', function ( WhatsAppService $whatsapp) {
    $token = env('WHATSAPP_API_TOKEN');

    $response = $whatsapp->checkConnection($token);

    return response()->json($response->json());
})->name('whatsapp.status');

/**
 * ===========================================================
 * | Rota para Uniciar o WhatsApp
 * ===========================================================
 */
Route::get('/whatsapp-connect', function(WhatsAppService $whatsapp){
    $token = env('WHATSAPP_API_TOKEN');

    $response = $whatsapp->startWhatsApp($token);
    return response()->json($response->json());
})->name('whatsapp.connect');

/**
 * ==================================
 * Rotas de página                  =
 * ==================================
 */
Route::get('/page/single-contact', [SingleContactController::class, 'index'])->name('page.single.contact');
Route::get('/adm/page/user', [UserController::class, 'index'])->name('adm.user');

