@extends('layout.app')

@section('content')

    @if($connected)
        <div class="alert alert-success">✅ Conectado ao WhatsApp</div>
    @elseif($qrcode)
        <p>Escaneie o QR Code com o WhatsApp</p>
        <img src="{{ asset('storage/' . $qrcode) }}" style="max-width: 300px;">
    @else
        <p>Inicie a conexão para gerar o QR Code.</p>
    @endif

    {{-- Modal --}}
    <div class="modal fade" id="modalConexao" tabindex="-1" aria-labelledby="modalConexao" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConexaoLabel">Conectar WhatsApp</h5>
                </div>
                <div class="modal-body">
                    <p>Abra o WhatsApp e prepare-se para escanear o QR Code quando ele aparecer.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <a href="{{ route('home') }}" class="btn btn-secondary">Cancelar</a>
                    <form method="POST" action="{{ route('new.instance') }}">
                        @csrf
                        <button type="submit" class="btn btn-success">Estou pronto</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
    @if($mostrar_modal ?? !$mostrar2_modal)
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var modal = new bootstrap.Modal(document.getElementById('modalConexao'));
                modal.show();
            });
        </script>
    @endif
@endpush
@endsection

{{-- <script>
    // Verifica se o CSRF token existe antes de configurar
    const csrfToken = document.querySelector('meta[name="csrf-token"]');

    if (csrfToken && typeof axios !== 'undefined') {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
    }

    // Estados da aplicação
    const AppStates = {
        LOADING: 'loading',
        CONNECTED: 'connected',
        DISCONNECTED_WITH_QR: 'disconnected_with_qr',
        DISCONNECTED_NO_QR: 'disconnected_no_qr',
        ERROR: 'error'
    };

    let currentState = AppStates.LOADING;

    // Elementos DOM (serão inicializados quando o DOM carregar)
    const elements = {};

    function initializeElements() {
        elements.status = document.getElementById('status');
        elements.qrContainer = document.getElementById('qr-container');
        elements.btnRestart = document.getElementById('btn-restart');
        elements.btnDisconnect = document.getElementById('btn-disconnect');

        console.log('Elementos inicializados:', elements);
        return elements.status && elements.qrContainer && elements.btnRestart && elements.btnDisconnect;
    }

    function showLoading(message = '⏳ Verificando conexão...') {
        if (!elements.status) return;
        elements.status.innerHTML = message;
        elements.qrContainer.innerHTML = '';
        hideAllButtons();
    }

    function hideAllButtons() {
        if (elements.btnRestart) elements.btnRestart.classList.add('d-none');
        if (elements.btnDisconnect) elements.btnDisconnect.classList.add('d-none');
    }

    function showConnectedState() {
        if (!elements.status) return;
        currentState = AppStates.CONNECTED;
        elements.status.innerHTML = '✅ WhatsApp conectado';
        elements.qrContainer.innerHTML = '';
        if (elements.btnDisconnect) elements.btnDisconnect.classList.remove('d-none');
        if (elements.btnRestart) elements.btnRestart.classList.add('d-none');
    }

    function showDisconnectedWithQrState(qrCodeBase64) {
        if (!elements.status) return;
        currentState = AppStates.DISCONNECTED_WITH_QR;
        elements.status.innerHTML = '📱 Escaneie o QR Code para conectar';
        elements.qrContainer.innerHTML = `<img src="${qrCodeBase64}" width="250" height="250" alt="QR Code WhatsApp">`;
        hideAllButtons();
    }

    function showDisconnectedNoQrState() {
        if (!elements.status) return;
        currentState = AppStates.DISCONNECTED_NO_QR;
        elements.status.innerHTML = '❌ WhatsApp desconectado';
        elements.qrContainer.innerHTML = '<span class="text-warning">QR Code expirado ou não disponível.</span>';
        if (elements.btnRestart) elements.btnRestart.classList.remove('d-none');
        if (elements.btnDisconnect) elements.btnDisconnect.classList.add('d-none');
    }

    function showErrorState(message = 'Erro ao verificar status da conexão') {
        if (!elements.status) return;
        currentState = AppStates.ERROR;
        elements.status.innerHTML = '⚠️ ' + message;
        elements.qrContainer.innerHTML = '';
        if (elements.btnRestart) elements.btnRestart.classList.remove('d-none');
        if (elements.btnDisconnect) elements.btnDisconnect.classList.add('d-none');
    }

    async function verificarStatus() {
        console.log('Verificando status...');

        if (typeof axios === 'undefined') {
            console.error('Axios não está carregado');
            showErrorState('Erro: Biblioteca de requisições não carregada');
            return;
        }

        if (!elements.status && !initializeElements()) {
            console.error('Falha ao inicializar elementos DOM');
            return;
        }

        try {
            console.log('Fazendo requisição para /whatsapp/status');
            const statusResponse = await axios.get('/whatsapp/status');
            console.log('Status response:', statusResponse.data);

            const connectionStatus = statusResponse.data.status;

            switch (connectionStatus) {
                case 'CONNECTED':
                    showConnectedState();
                    return;
                case 'NOT_INITIALIZED':
                    showErrorState('Instância não inicializada');
                    return;
                case 'AWAITING_FIRST_CONNECTION':
                    console.log('Primeira conexão - gerando QR Code...');
                    await generateInitialQrCode();
                    return;
                case 'DISCONNECTED':
                    console.log('Desconectado - tentando obter QR Code...');
                    await getQrCode();
                    return;
                default:
                    showDisconnectedNoQrState();
            }

        } catch (statusError) {
            console.error('Erro ao verificar status:', statusError);

            // Se erro 400, pode ser primeira conexão
            if (statusError.response?.status === 400) {
                console.log('Erro 400 - tentando gerar QR Code inicial...');
                await generateInitialQrCode();
            } else {
                showErrorState('Erro de conexão com o servidor');
            }
        }
    }

    async function generateInitialQrCode() {
        try {
            const qrResponse = await axios.post('/whatsapp/generate-initial-qr');
            console.log('QR inicial response:', qrResponse.data);

            if (qrResponse.data.qrcode_base64) {
                showDisconnectedWithQrState(qrResponse.data.qrcode_base64);
            } else {
                showDisconnectedNoQrState();
            }
        } catch (error) {
            console.error('Erro ao gerar QR Code inicial:', error);
            showErrorState('Erro ao gerar QR Code');
        }
    }

    async function getQrCode() {
        try {
            const qrResponse = await axios.get('/whatsapp/qrcode');
            console.log('QR response:', qrResponse.data);

            if (qrResponse.data.qrcode_base64) {
                showDisconnectedWithQrState(qrResponse.data.qrcode_base64);
            } else {
                showDisconnectedNoQrState();
            }
        } catch (error) {
            console.error('Erro ao buscar QR Code:', error);
            showDisconnectedNoQrState();
        }
    }

    async function restartQrCode() {
        try {
            showLoading('⏳ Reiniciando instância...');

            await axios.post('/whatsapp/restart');

            // Aguarda um pouco para o webhook processar
            setTimeout(async () => {
                showLoading('⏳ Gerando novo QR Code...');

                // Tenta algumas vezes pegar o novo QR
                let attempts = 0;
                const maxAttempts = 10;

                const checkForNewQr = async () => {
                    attempts++;

                    try {
                        const qrResponse = await axios.get('/whatsapp/qrcode');

                        if (qrResponse.data.qrcode_base64) {
                            showDisconnectedWithQrState(qrResponse.data.qrcode_base64);
                        } else if (attempts < maxAttempts) {
                            setTimeout(checkForNewQr, 1000);
                        } else {
                            showErrorState('Timeout ao gerar QR Code');
                        }
                    } catch (error) {
                        if (attempts < maxAttempts) {
                            setTimeout(checkForNewQr, 1000);
                        } else {
                            showErrorState('Não foi possível gerar o QR Code');
                        }
                    }
                };

                checkForNewQr();
            }, 2000);

        } catch (error) {
            console.error('Erro ao reiniciar instância:', error);
            showErrorState('Erro ao reiniciar instância');
        }
    }

    async function disconnect() {
        if (!confirm('Tem certeza que deseja desconectar o WhatsApp?')) {
            return;
        }

        try {
            showLoading('⏳ Desconectando...');

            await axios.post('/whatsapp/disconnect');

            setTimeout(() => {
                alert('Desconectado com sucesso!');
                verificarStatus();
            }, 1000);

        } catch (error) {
            console.error('Erro ao desconectar:', error);
            alert('Erro ao desconectar. Tente novamente.');
            verificarStatus();
        }
    }

    // Inicialização
    document.addEventListener('DOMContentLoaded', function () {
        console.log('DOM carregado, inicializando...');

        // Tenta inicializar os elementos
        if (initializeElements()) {
            console.log('Elementos inicializados com sucesso');
            showLoading();

            // Aguarda um pouco e verifica o status
            setTimeout(() => {
                console.log('Executando primeira verificação...');
                verificarStatus();
            }, 1000);

            // Verifica o status a cada 5 segundos
            setInterval(() => {
                if (currentState !== AppStates.LOADING) {
                    verificarStatus();
                }
            }, 5000);
        } else {
            console.error('Falha ao inicializar elementos DOM');
        }
    });

    // Fallback para navegadores antigos
    if (document.readyState === 'loading') {
        // DOM ainda não carregou
    } else {
        // DOM já carregou
        setTimeout(() => {
            if (!elements.status) {
                console.log('Executando fallback de inicialização...');
                initializeElements();
                verificarStatus();
            }
        }, 500);
    }
</script> --}}
{{-- <div>

    <h1 class="text-center">QRCODE</h1>
    <hr>
    @if (!$haInstancia)
        <div class="text-center">
            <div>
                <p>Seja bem vindo a sua primeira conexão!</p>
            </div>
            <button id="conectarwgw" class="btn btn-success">Conectar ao WhatsApp</button>

        </div>
    @endif
</div> --}}

{{-- <div class="container mt-5 d-flex justify-content-center">
        <div class="card shadow-lg p-4" style="width: 400px;">
            <h4 class="mb-3 text-center">Conectar WhatsApp</h4>

            <div id="qr-container" class="d-flex justify-content-center flex-column align-items-center mb-3"></div>
            <div id="status" class="text-center fw-bold mb-3"></div>

            <div class="d-grid gap-2">
                <button id="btn-restart" class="btn btn-warning d-none" onclick="restartQrCode()">
                    🔄 Gerar Novo QR Code
                </button>
                <button id="btn-disconnect" class="btn btn-danger d-none" onclick="disconnect()">
                    🔌 Desconectar
                </button>
                <!-- Botão de debug (remover em produção) -->
                <button id="btn-debug" class="btn btn-info btn-sm" onclick="verificarStatus()">
                    🔍 Debug - Verificar Status
                </button>
            </div>
        </div>
    </div> --}}
{{-- <script>
    // Verifica se o CSRF token existe antes de configurar
    const csrfToken = document.querySelector('meta[name="csrf-token"]');

    if (csrfToken && typeof axios !== 'undefined') {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
    }

    // Estados da aplicação
    const AppStates = {
        LOADING: 'loading',
        CONNECTED: 'connected',
        DISCONNECTED_WITH_QR: 'disconnected_with_qr',
        DISCONNECTED_NO_QR: 'disconnected_no_qr',
        ERROR: 'error'
    };

    let currentState = AppStates.LOADING;

    // Elementos DOM (serão inicializados quando o DOM carregar)
    const elements = {};

    function initializeElements() {
        elements.status = document.getElementById('status');
        elements.qrContainer = document.getElementById('qr-container');
        elements.btnRestart = document.getElementById('btn-restart');
        elements.btnDisconnect = document.getElementById('btn-disconnect');

        console.log('Elementos inicializados:', elements);
        return elements.status && elements.qrContainer && elements.btnRestart && elements.btnDisconnect;
    }

    function showLoading(message = '⏳ Verificando conexão...') {
        if (!elements.status) return;
        elements.status.innerHTML = message;
        elements.qrContainer.innerHTML = '';
        hideAllButtons();
    }

    function hideAllButtons() {
        if (elements.btnRestart) elements.btnRestart.classList.add('d-none');
        if (elements.btnDisconnect) elements.btnDisconnect.classList.add('d-none');
    }

    function showConnectedState() {
        if (!elements.status) return;
        currentState = AppStates.CONNECTED;
        elements.status.innerHTML = '✅ WhatsApp conectado';
        elements.qrContainer.innerHTML = '';
        if (elements.btnDisconnect) elements.btnDisconnect.classList.remove('d-none');
        if (elements.btnRestart) elements.btnRestart.classList.add('d-none');
    }

    function showDisconnectedWithQrState(qrCodeBase64) {
        if (!elements.status) return;
        currentState = AppStates.DISCONNECTED_WITH_QR;
        elements.status.innerHTML = '📱 Escaneie o QR Code para conectar';
        elements.qrContainer.innerHTML = `<img src="${qrCodeBase64}" width="250" height="250" alt="QR Code WhatsApp">`;
        hideAllButtons();
    }

    function showDisconnectedNoQrState() {
        if (!elements.status) return;
        currentState = AppStates.DISCONNECTED_NO_QR;
        elements.status.innerHTML = '❌ WhatsApp desconectado';
        elements.qrContainer.innerHTML = '<span class="text-warning">QR Code expirado ou não disponível.</span>';
        if (elements.btnRestart) elements.btnRestart.classList.remove('d-none');
        if (elements.btnDisconnect) elements.btnDisconnect.classList.add('d-none');
    }

    function showErrorState(message = 'Erro ao verificar status da conexão') {
        if (!elements.status) return;
        currentState = AppStates.ERROR;
        elements.status.innerHTML = '⚠️ ' + message;
        elements.qrContainer.innerHTML = '';
        if (elements.btnRestart) elements.btnRestart.classList.remove('d-none');
        if (elements.btnDisconnect) elements.btnDisconnect.classList.add('d-none');
    }

    async function verificarStatus() {
        console.log('Verificando status...');

        if (typeof axios === 'undefined') {
            console.error('Axios não está carregado');
            showErrorState('Erro: Biblioteca de requisições não carregada');
            return;
        }

        if (!elements.status && !initializeElements()) {
            console.error('Falha ao inicializar elementos DOM');
            return;
        }

        try {
            console.log('Fazendo requisição para /whatsapp/status');
            const statusResponse = await axios.get('/whatsapp/status');
            console.log('Status response:', statusResponse.data);

            const connectionStatus = statusResponse.data.status;

            switch (connectionStatus) {
                case 'CONNECTED':
                    showConnectedState();
                    return;
                case 'NOT_INITIALIZED':
                    showErrorState('Instância não inicializada');
                    return;
                case 'AWAITING_FIRST_CONNECTION':
                    console.log('Primeira conexão - gerando QR Code...');
                    await generateInitialQrCode();
                    return;
                case 'DISCONNECTED':
                    console.log('Desconectado - tentando obter QR Code...');
                    await getQrCode();
                    return;
                default:
                    showDisconnectedNoQrState();
            }

        } catch (statusError) {
            console.error('Erro ao verificar status:', statusError);

            // Se erro 400, pode ser primeira conexão
            if (statusError.response?.status === 400) {
                console.log('Erro 400 - tentando gerar QR Code inicial...');
                await generateInitialQrCode();
            } else {
                showErrorState('Erro de conexão com o servidor');
            }
        }
    }

    async function generateInitialQrCode() {
        try {
            const qrResponse = await axios.post('/whatsapp/generate-initial-qr');
            console.log('QR inicial response:', qrResponse.data);

            if (qrResponse.data.qrcode_base64) {
                showDisconnectedWithQrState(qrResponse.data.qrcode_base64);
            } else {
                showDisconnectedNoQrState();
            }
        } catch (error) {
            console.error('Erro ao gerar QR Code inicial:', error);
            showErrorState('Erro ao gerar QR Code');
        }
    }

    async function getQrCode() {
        try {
            const qrResponse = await axios.get('/whatsapp/qrcode');
            console.log('QR response:', qrResponse.data);

            if (qrResponse.data.qrcode_base64) {
                showDisconnectedWithQrState(qrResponse.data.qrcode_base64);
            } else {
                showDisconnectedNoQrState();
            }
        } catch (error) {
            console.error('Erro ao buscar QR Code:', error);
            showDisconnectedNoQrState();
        }
    }

    async function restartQrCode() {
        try {
            showLoading('⏳ Reiniciando instância...');

            await axios.post('/whatsapp/restart');

            // Aguarda um pouco para o webhook processar
            setTimeout(async () => {
                showLoading('⏳ Gerando novo QR Code...');

                // Tenta algumas vezes pegar o novo QR
                let attempts = 0;
                const maxAttempts = 10;

                const checkForNewQr = async () => {
                    attempts++;

                    try {
                        const qrResponse = await axios.get('/whatsapp/qrcode');

                        if (qrResponse.data.qrcode_base64) {
                            showDisconnectedWithQrState(qrResponse.data.qrcode_base64);
                        } else if (attempts < maxAttempts) {
                            setTimeout(checkForNewQr, 1000);
                        } else {
                            showErrorState('Timeout ao gerar QR Code');
                        }
                    } catch (error) {
                        if (attempts < maxAttempts) {
                            setTimeout(checkForNewQr, 1000);
                        } else {
                            showErrorState('Não foi possível gerar o QR Code');
                        }
                    }
                };

                checkForNewQr();
            }, 2000);

        } catch (error) {
            console.error('Erro ao reiniciar instância:', error);
            showErrorState('Erro ao reiniciar instância');
        }
    }

    async function disconnect() {
        if (!confirm('Tem certeza que deseja desconectar o WhatsApp?')) {
            return;
        }

        try {
            showLoading('⏳ Desconectando...');

            await axios.post('/whatsapp/disconnect');

            setTimeout(() => {
                alert('Desconectado com sucesso!');
                verificarStatus();
            }, 1000);

        } catch (error) {
            console.error('Erro ao desconectar:', error);
            alert('Erro ao desconectar. Tente novamente.');
            verificarStatus();
        }
    }

    // Inicialização
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM carregado, inicializando...');

        // Tenta inicializar os elementos
        if (initializeElements()) {
            console.log('Elementos inicializados com sucesso');
            showLoading();

            // Aguarda um pouco e verifica o status
            setTimeout(() => {
                console.log('Executando primeira verificação...');
                verificarStatus();
            }, 1000);

            // Verifica o status a cada 5 segundos
            setInterval(() => {
                if (currentState !== AppStates.LOADING) {
                    verificarStatus();
                }
            }, 5000);
        } else {
            console.error('Falha ao inicializar elementos DOM');
        }
    });

    // Fallback para navegadores antigos
    if (document.readyState === 'loading') {
        // DOM ainda não carregou
    } else {
        // DOM já carregou
        setTimeout(() => {
            if (!elements.status) {
                console.log('Executando fallback de inicialização...');
                initializeElements();
                verificarStatus();
            }
        }, 500);
    }
</script> --}}

{{--    --}}{{-- @if (session('success'))
        <x-alert type="success" :message="session('success')"/>
    @endif

    @if (session('error'))
        <x-alert type="error" :message="session('error')"/>
    @endif
 --}}{{--
    <div class="container mt-5 d-flex justify-content-center">
        <div class="card shadow-lg p-4" style="width: 400px;">
            <h4 class="mb-3 text-center">Conectar WhatsApp</h4>

            <h2>Status do WhatsApp:
                <span id="status-text" style="font-weight: bold; color: gray;">
                    Desconhecido
                </span>
            </h2>

            <div id="messages" style="border: 1px solid #ccc; padding: 10px; height: 200px; margin: 10px 0;">
                <p>Aguardando mensagens...</p>
            </div>

            <div id="qr-container" class="d-flex justify-content-center flex-column align-items-center mb-3"></div>
            <div id="status" class="text-center fw-bold mb-3"></div>

            <button id="btn-desconectar" class="btn btn-danger btn-sm mt-2 d-none">
                ❌ Desconectar
            </button>

            <div class="d-grid gap-2">

                <!-- Botão de debug (remover em produção) -->
                <button id="btn-nova-conexao" class="btn btn-info btn-sm" onclick="novaConexaoGWG()">
                    Nova conexão
                </button>

                <button id="btn-resetar" class="btn btn-warning btn-sm d-none mt-2">
                    🔁 Resetar QR Code
                </button>

            </div>
        </div>
    </div>

    <div class="container">
        <h1>Status da Conexão WhatsApp</h1>

        <div id="status-display" style="border: 1px solid #ccc; padding: 20px; margin: 20px 0; border-radius: 5px;">
            <p><strong>Status:</strong> <span id="current-status">Carregando...</span></p>
            <p><strong>Número:</strong> <span id="current-number">-</span></p>
            <p><small>Última atualização: <span id="last-update">-</span></small></p>
        </div>

        <div id="debug-info" style="background: #f5f5f5; padding: 10px; margin: 10px 0; border-radius: 5px;">
            <h4>Debug Info:</h4>
            <p>Echo conectado: <span id="echo-status">Verificando...</span></p>
            <p>Canal conectado: <span id="channel-status">Verificando...</span></p>
            <div id="console-logs" style="font-family: monospace; background: white; padding: 10px; max-height: 200px; overflow-y: auto;"></div>
        </div>

        <button onclick="window.location.reload()" class="btn btn-primary">
            Atualizar Status
        </button>
    </div>

    <script>
        // Função para adicionar logs na tela
        function addLog(message) {
            const logDiv = document.getElementById('console-logs');
            const time = new Date().toLocaleTimeString();
            logDiv.innerHTML += `<div>[${time}] ${message}</div>`;
            logDiv.scrollTop = logDiv.scrollHeight;
            console.log(message);
        }

        // Verificar se Echo está disponível
        if (typeof Echo !== 'undefined') {
            addLog('✅ Echo está disponível');
            document.getElementById('echo-status').textContent = 'Conectado';

            // Conectar ao canal
            const channel = Echo.channel('status-conexao');
            addLog('🔗 Conectando ao canal "status-conexao"...');

            // Verificar conexão do canal
            channel.subscribed(() => {
                addLog('✅ Canal "status-conexao" conectado com sucesso');
                document.getElementById('channel-status').textContent = 'Conectado';
            });

            channel.error((error) => {
                addLog('❌ Erro no canal: ' + JSON.stringify(error));
                document.getElementById('channel-status').textContent = 'Erro: ' + error;
            });

            // Escutar o evento
            channel.listen('.status.atualizado', (e) => {
                addLog('📨 Evento recebido: ' + JSON.stringify(e));

                // Atualizar a interface
                document.getElementById('current-status').textContent = e.status;
                document.getElementById('current-number').textContent = e.number;
                document.getElementById('last-update').textContent = new Date().toLocaleString();

                // Mudar cor baseado no status
                const statusDisplay = document.getElementById('status-display');
                if (e.status === 'connected') {
                    statusDisplay.style.borderColor = 'green';
                    statusDisplay.style.backgroundColor = '#f0fff0';
                } else {
                    statusDisplay.style.borderColor = 'red';
                    statusDisplay.style.backgroundColor = '#fff0f0';
                }
            });

            // Escutar qualquer evento no canal (para debug)
            channel.listen('*', (eventName, data) => {
                addLog('🔍 Evento capturado: ' + eventName + ' - Data: ' + JSON.stringify(data));
            });

        } else {
            addLog('❌ Echo não está disponível');
            document.getElementById('echo-status').textContent = 'Não disponível';
        }
    </script>
    <script>
        const qrContainer = document.getElementById('qr-container');
        const statusDiv = document.getElementById('status');
        const btnResetar = document.getElementById('btn-resetar');

        btnResetar.classList.add('d-none'); // Oculta o botão no início

        document.getElementById('btn-resetar').addEventListener('click', async () => {
            try {
                const confirmacao = confirm('Deseja realmente resetar a conexão?');
                if (!confirmacao) return;

                const res = await axios.post('/resetar-instancia');
                alert(res.data.message || 'Instância reiniciada.');

                carregarQRCode();
                verificarStatus();
            } catch (err) {
                alert('Erro ao resetar instância!');
                console.error(err);
            }
        });
        document.getElementById('btn-desconectar').addEventListener('click', async () => {
            if (!confirm('Deseja desconectar do WhatsApp?')) return;

            try {
                const res = await axios.post('/desconectar');
                alert(res.data.message || 'Desconectado com sucesso!');
                carregarQRCode();
                verificarStatus();
            } catch (err) {
                alert('Erro ao desconectar!');
                console.error(err);
            }
        });

        function novaConexaoGWG() {

        }

        async function carregarQRCode() {
            try {
                const res = await axios.get('/gerar-qrcode');
                const qrBase64 = res.data.qrcode_base64;

                qrContainer.innerHTML =
                    `<img src="${qrBase64}" alt="QR Code" class="img-fluid" style="max-width: 300px;">`;
                qrContainer.classList.remove('d-none'); // Garante que apareça se estiver oculto
            } catch (error) {
                qrContainer.innerHTML = `<p class="text-info">⏳ QR Code ainda não disponível. Aguarde...</p>`;
                qrContainer.classList.remove('d-none');
            }
        }

        let conectado = false;

        async function verificarStatus() {

            const res = await axios.get('/status-conexao');
            //const status = res.data.status;

            console.log(res);
            try {
                const res = await axios.get('/status-conexao');
                const status = res.data.status

                console.log(status);

                if (status === 'connected') {
                    conectado = true;
                    statusDiv.innerHTML = `<span class="text-success">✅ Conectado com sucesso!</span>`;
                    btnResetar.classList.add('d-none');
                    //document.getElementById('btn-desconectar').classList.remove('d-none');
                    qrContainer.innerHTML = ''; // Oculta QR
                } else {
                    conectado = false;
                    statusDiv.innerHTML = `<span class="text-warning">🔄 Status: ${status}</span>`;
                    btnResetar.classList.remove('d-none');
                    //document.getElementById('btn-desconectar').classList.add('d-none');
                }
            } catch (err) {
                conectado = false;
                statusDiv.innerHTML = `<span class="text-danger">Aguarde...</span>`;
                btnResetar.classList.remove('d-none');
                console.error(err);
            }
        }

        // Atualizações a cada 5s
        setInterval(() => {
            if (!conectado) {
                carregarQRCode();
            }
            verificarStatus();
        }, 5000);

        // Execução inicial
        verificarStatus();
        carregarQRCode();
    </script>--}}
