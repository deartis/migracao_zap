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
use App\Models\Instances;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

// Rota segura para gerar Token
Route::middleware(['auth'])->get('/whatsapp/token', function () {
    $user = auth()->user();
    $token = Crypt::encryptString("user-{$user->id}");

    return response()->json(['token' => $token]);
});

// Rota para receber QR Code via Webhook

Route::post('/webhookqrcode', [WebhookController::class, 'webhooks'])->name('webhook.qrcode');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    //Teste de novo método simples
    Route::get('/whatsapp/connect', [WhatsAppController::class, 'showQRCode'])->name('whatsapp.connect');

    Route::middleware(['check.instance'])->group(function () {
        Route::get('/page/single-contact', [SingleContactController::class, 'index'])->name('page.single.contact');
        Route::post('/page/single-contact/send', [SingleContactController::class, 'send'])->name('page.single.contact.send');
        Route::post('/contact-chat', [SingleContactController::class, 'importarChats'])->name('contact.chat');

        Route::get('/live', [ToRespondMsgController::class, 'index'])->name('page.respond.msg');
        Route::get('/historic', [HistoricController::class, 'index'])->name('page.historic');

        Route::get('/page/from-sheet', [InportListController::class, 'index'])->name('page.from.sheet');
        Route::post('/envia-mensagem-em-massa-lista', [InportListController::class, 'enviaMensagemEmMassaLista'])->name('mensagem.em.massa.lista');
        Route::get('/get-tpl', [InportListController::class, 'getTemplate']);
        Route::get('/envio-progresso', [InportListController::class, 'envioProgresso']);
        Route::post('/reseta-progresso', [InportListController::class, 'resetaProgresso']);
        Route::post('/envia-mensagem-em-massa-contatos', [MultipleContactMsgController::class, 'enviaMensagemContatosWhatsapp'])->name('enviar.mensagem.massa.contatos');

        // Interrompe o envio de mensagem em massa do job
        Route::post('/interromper-envio', [InportListController::class, 'interromper']);

        Route::get('/chats-ativos', function () {
            $userId = auth()->id(); // ou como você obtém o ID do usuário
            $chats = \Illuminate\Support\Facades\Cache::get("chats_ativos_$userId", []);

            Log::info("Veio Aqui Essa Praga");
            return response()->json([
                'success' => true,
                'chats' => $chats
            ]);
        })->name('chats.ativos');

        Route::get('/page/multiple-msg', [MultipleContactMsgController::class, 'index'])->name('page.multi.msg');
        //Route::get('/whatsapp/chats/json', [MultipleContactMsgController::class, 'getChatsJson'])->name('whatsapp.chats.json');
        //Route::post('/whatsapp/enviar', [MultipleContactMsgController::class, 'enviarMensagem'])->name('whatsapp.enviar');

        Route::get('/dashboard', [WhatsAppController::class, 'dashboard'])->name('dashboard');
        Route::get('/contatos', [WhatsAppController::class, 'getContacts'])->name('get.contatos');
    });

    Route::get('/whatsapp/status', [WhatsAppController::class, 'status'])->name('whatsapp.status');

    Route::get('/connection', [ConnectionController::class, 'index'])->name('page.connection');
    Route::post('/new-instance', [ConnectionController::class, 'newInstance'])->name('new.instance');
    /*Route::get('/conectarwgw', [ConnectionController::class, 'conectarWgw']);
    Route::get('/gerar-qrcode', [ConnectionController::class, 'gerarQrCode']);*/
    Route::get('/status-conexao', [ConnectionController::class, 'status']);
    //Route::post('/resetar-instancia', [ConnectionController::class, 'resetarInstancia']);
    //Route::post('/desconectar', [ConnectionController::class, 'desconectar']);

    Route::get('/whatsapp/qrcode', [ConnectionController::class, 'gerarQrCode']);
    Route::get('/whatsapp/restart', [ConnectionController::class, 'restartInstance'])->name('instancia.reiniciar');
    //Route::post('/whatsapp/generate-initial-qr', [ConnectionController::class, 'generateInitialQrCode']);

    /*Route::get('/whatsapp/status', [ConnectionController::class, 'status'])->name('whatsapp.status');
    Route::post('/whatsapp/disconnect', [ConnectionController::class, 'disconnect']);
    Route::post('/whatsapp/generate-initial-qr', [ConnectionController::class, 'generateInitialQrCode'])
        ->middleware('auth');*/

    /*Route::get('/whatsapp/status', [ConnectionController::class, 'verificarStatus']);*/

    // routes/web.php
    Route::get('/qrcode-atualizar', function () {

        $instance = Instances::where('user_id',auth()->id())->first(); // ou filtre por usuário
        return response()->json([
            'connected' => $instance->connected,
            'expired' => $instance->expired_qrcode,
            'qrcode' => $instance->qrcode ? asset('storage/' . $instance->qrcode) : null,
        ]);
    });
   /* Route::get('/qrcode-atualizar', function () {
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
    })->name('qrcode.atualizar');*/

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

    Broadcast::routes();
});
