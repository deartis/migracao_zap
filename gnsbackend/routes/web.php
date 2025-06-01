<?php

use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\HistoricController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InportListController;
use App\Http\Controllers\MultipleContactMsgController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SingleContactController;
use App\Http\Controllers\ToRespondMsgController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

// Rota segura para gerar Token
Route::middleware(['auth'])->get('/whatsapp/token', function () {
    $user = auth()->user();
    $token = Crypt::encryptString("user-{$user->id}");

    return response()->json(['token' => $token]);
});

// Rota para receber QR Code via Webhook
Route::post('/webhookqrcode', [WebhookController::class, 'receberQrCode'])->name('webhook.qrcode');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::middleware(['check.instance'])->group(function () {
        Route::get('/page/single-contact', [SingleContactController::class, 'index'])->name('page.single.contact');
        Route::post('/page/single-contact/send', [SingleContactController::class, 'send'])->name('page.single.contact.send');
        Route::post('/contact-chat', [SingleContactController::class, 'importarChats'])->name('contact.chat');

        Route::post('/whatsapp-send', [WhatsAppController::class, 'sendMessage'])->name('whatsapp.send');
        Route::post('/whatsapp-send-bulk', [WhatsAppController::class, 'sendBulkMessages'])->name('whatsapp.send.bulk');
        Route::get('/live', [ToRespondMsgController::class, 'index'])->name('page.respond.msg');
        Route::post('/responder', [WhatsAppController::class, 'responder'])->name('whatsapp.responder');
        Route::get('/historic', [HistoricController::class, 'index'])->name('page.historic');

        Route::get('/page/from-sheet', [InportListController::class, 'index'])->name('page.from.sheet');
        Route::post('/envia-mensagem-em-massa-lista', [InportListController::class, 'enviaMensagemEmMassaLista'])->name('mensagem.em.massa.lista');
        Route::get('/get-tpl', [InportListController::class, 'getTemplate']);
        Route::get('/envio-progresso', [InportListController::class, 'envioProgresso']);
        Route::post('/reseta-progresso', [InportListController::class, 'resetaProgresso']);
        Route::post('/envia-mensagem-em-massa-contstos', [MultipleContactMsgController::class, 'enviaMensagemContatosWhatsapp']);

        Route::get('/page/multiple-msg', [MultipleContactMsgController::class, 'index'])->name('page.multi.msg');
        Route::get('/dashboard', [WhatsAppController::class, 'dashboard'])->name('dashboard');
    });

    Route::get('/connection', [ConnectionController::class, 'index'])->name('page.connection');
    Route::get('/whatsapp/status', [ConnectionController::class, 'status']);
    Route::get('/whatsapp/qrcode', [ConnectionController::class, 'qrcode']);
    Route::post('/whatsapp/restart', [ConnectionController::class, 'restartInstance']);
    Route::post('/whatsapp/disconnect', [ConnectionController::class, 'disconnect']);
    Route::post('/whatsapp/generate-initial-qr', [ConnectionController::class, 'generateInitialQrCode'])
        ->middleware('auth');

    /*Route::get('/whatsapp/status', [ConnectionController::class, 'verificarStatus']);
    Route::get('/whatsapp/qrcode', [ConnectionController::class, 'gerarQrCode']);*/

    Route::get('/qrcode-atualizar', function () {
        $diretorio = storage_path('app/public/qrcodes');
        $arquivos = \Illuminate\Support\Facades\File::files($diretorio);

        if (empty($arquivos)) {
            return response()->json(['qrcode_url' => null]);
        }

        usort($arquivos, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $nomeArquivo = basename($arquivos[0]);
        $urlQrCode = asset('storage/qrcodes/' . $nomeArquivo);

        return response()->json(['qrcode_url' => $urlQrCode]);
    })->name('qrcode.atualizar');

    Route::get('/profile', [ProfileController::class, 'index'])->name('page.profile');
    Route::put('/profile/{user}', [ProfileController::class, 'update'])->name('page.update.profile');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::middleware(['auth', 'role:admin'])->group(function () {
        // Rotas de admins
        Route::get('/adm/page/user', [UserController::class, 'index'])->name('adm.user');
        Route::get('/adm/page/register-user', [UserController::class, 'registerUser'])->name('adm.register.user');
        Route::post('/adm/page/register-user', [UserController::class, 'store'])->name('add.user');

        Route::resource('users', UserController::class);
    });
});
