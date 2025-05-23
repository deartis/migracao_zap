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
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Route;


//Rota segura para gerar Token
Route::middleware(['auth'])->get('/whatsapp/token', function(){
    $user = auth()->user();
    $token = Crypt::encryptString("user-{$user->id}");

    return response()->json(['token' => $token]);
});


Route::middleware(['auth'])->group(function () {
    Route::get('/', [HomeController::class,'index'])->name('home');

    // Rota pública para verificação de saúde (opcional)
    Route::get('/whatsapp/health', [WhatsAppController::class, 'healthCheck']);
    Route::get('/whatsapp-status', [WhatsAppController::class, 'checkConnection']);
    Route::get('/whatsapp-connect', [WhatsAppController::class, 'startWhatsApp']);

    //Rotas comuns
    Route::get('/page/single-contact', [SingleContactController::class, 'index'])->name('page.single.contact');
    Route::post('/page/single-contact/send', [SingleContactController::class, 'send'])->name('page.single.contact.send');
    Route::post('/contact-chat', [SingleContactController::class, 'importarChats'])->name('contact.chat');


    Route::post('/whatsapp-send', [WhatsAppController::class, 'sendMessage'])->name('whatsapp.send');
    Route::post('/whatsapp-send-bulk', [WhatsAppController::class, 'sendBulkMessages'])->name('whatsapp.send.bulk');
    Route::get('/live', [ToRespondMsgController::class, 'index'])->name('page.respond.msg');
    Route::post('/responder', [WhatsAppController::class, 'responder'])->name('whatsapp.responder');
    Route::get('/historic', [HistoricController::class, 'index'])->name('page.historic');

    Route::get('/profile', [ProfileController::class, 'index'])->name('page.profile');
    Route::put('/profile/{user}', [ProfileController::class, 'update'])->name('page.update.profile');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');


    Route::get('/page/from-sheet',[InportListController::class, 'index'])->name('page.from.sheet');
    //Route::post('/page/from-sheet',[InportListController::class, 'uploadSheet'])->name('upload.sheet');
    Route::post('/envia-mensagem-em-massa-lista', [InportListController::class, 'enviaMensagemEmMassaLista'])->name('mensagem.em.massa.lista');
    Route::get('/get-tpl', [InportListController::class, 'getTemplate']);
    Route::get('/envio-progresso', [InportListController::class, 'envioProgresso']);
    Route::post('/reseta-progresso', [InportListController::class, 'resetaProgresso']);
    Route::post('/envia-mensagem-em-massa-contstos', [MultipleContactMsgController::class, 'enviaMensagemContatosWhatsapp']);


    Route::get('/page/multiple-msg',[MultipleContactMsgController::class, 'index'])->name('page.multi.msg');
    Route::get('/dashboard', [WhatsAppController::class, 'dashboard'])->name('dashboard');
    Route::get('/connection', [ConnectionController::class, 'index'])->name('page.connection');

    Route::prefix('whatsapp')->group(function () {
        Route::get('/contacts', [ToRespondMsgController::class, 'getContacts']);
        Route::get('/messages/{contactId}', [ToRespondMsgController::class, 'getMessages']);
        Route::post('/send-message', [ToRespondMsgController::class, 'sendMessage']);
        Route::post('/webhook', [ToRespondMsgController::class, 'webhook']);
    });

    Route::middleware(['auth', 'role:admin'])->group(function(){
        //Rotas de admins
        Route::get('/adm/page/user', [UserController::class, 'index'])->name('adm.user');
        Route::get('/adm/page/register-user', [UserController::class, 'registerUser'])->name('adm.register.user');
        Route::post('/adm/page/register-user', [UserController::class, 'store'])->name('add.user');

        //CRUDE Users
        Route::resource('users', UserController::class);
    });

});

