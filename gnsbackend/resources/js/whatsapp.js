// Configuração inicial
document.addEventListener('DOMContentLoaded', function () {
    // Verificar se os elementos globais de status existem
    const globalStatusIndicator = document.getElementById('global-status-indicator');
    const globalStatusText = document.getElementById('global-status-text');
    const whatsappGlobalStatus = document.querySelector('.whatsapp-global-status');

    /*if (whatsappGlobalStatus) {
        whatsappGlobalStatus.addEventListener('click', function () {
            // Redirecionar para a página do WhatsApp
            window.location.href = '/'; // Ajuste para a rota correta
        });

        // Adicionar cursor pointer para indicar que é clicável
        //whatsappGlobalStatus.style.cursor = 'pointer';
    }*/

    if (globalStatusIndicator && globalStatusText) {
        // Verificar status inicialmente
        checkGlobalWhatsAppStatus();

        // Verificar a cada 30 segundos (intervalo maior para páginas globais)
        setInterval(checkGlobalWhatsAppStatus, 30000);
    }

    // Verifique se estamos na página do WhatsApp
    if (document.getElementById('connection-status')) {
        // Adicionar isto à sua requisição
        const csrfToken = document.cookie
            .split('; ')
            .find(row => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1];

        // Verifique se isso retorna um valor válido
        checkStatus();
        setInterval(checkStatus, 5000); // Verificar status a cada 5 segundos

        // Botões
        const connectBtn = document.getElementById('connect-btn');
        if (connectBtn) {
            connectBtn.addEventListener('click', connectWhatsApp);
        }

        const sendBtn = document.getElementById('send-btn');
        if (sendBtn) {
            sendBtn.addEventListener('click', sendMessage);
        }
    }
});

// Verificar status da conexão
async function checkStatus() {
    try {
        const response = await fetch('/whatsapp-status');
        const data = await response.json();

        const statusElement = document.getElementById('connection-status');
        const phoneElement = document.getElementById('phone-number');
        const statusIndicator = document.getElementById('status-indicator');

        // Atualizar status
        statusElement.textContent = data.status || 'Desconhecido';

        // Atualizar classes CSS baseado no status
        statusElement.className = 'badge';
        if (data.status === 'connected') {
            statusElement.classList.add('bg-success');
            document.getElementById('message-form').classList.remove('d-none');
            document.getElementById('qr-container').classList.add('d-none');

            if (data.phoneNumber) {
                phoneElement.textContent = `Número conectado: ${data.phoneNumber}`;
            }
        } else if (data.status === 'connecting') {
            statusElement.classList.add('bg-warning', 'text-dark');
        } else {
            statusElement.classList.add('bg-secondary');
            document.getElementById('message-form').classList.add('d-none');
        }
    } catch (error) {
        console.error('Erro ao verificar status:', error);
    }
}

// Função para verificar o status global do WhatsApp
async function checkGlobalWhatsAppStatus() {
    try {
        const response = await fetch('/whatsapp-status');
        const data = await response.json();

        // Obter elementos
        const statusCircle = document.querySelector('.status-circle');
        const statusText = document.getElementById('global-status-text');

        // Atualizar o texto do status
        statusText.textContent =
            data.status === 'connected' ? 'On-line' :
            data.status === 'connecting' ? 'Conectando...' :
                'Desconectado';

        // Atualizar a cor do círculo
        if (data.status === 'connected') {
            statusCircle.style.backgroundColor = '#198754'; // Verde
        } else if (data.status === 'connecting') {
            statusCircle.style.backgroundColor = '#ffc107'; // Amarelo
        } else {
            statusCircle.style.backgroundColor = '#dc3545'; // Vermelho
        }

    } catch (error) {
        console.error('Erro ao verificar status global:', error);
        // Em caso de erro, definir como desconectado
        const statusCircle = document.querySelector('.status-circle');
        if (statusCircle) {
            statusCircle.style.backgroundColor = '#dc3545'; // Vermelho
        }

        const statusText = document.getElementById('global-status-text');
        if (statusText) {
            statusText.textContent = 'Desconectado';
        }
    }
}

// Conectar ao WhatsApp e mostrar QR Code
async function connectWhatsApp() {
    try {
        document.getElementById('qr-container').classList.remove('d-none');
        document.getElementById('qr-placeholder').innerHTML = '<p class="text-muted">Carregando QR Code...</p>';

        const response = await fetch('/whatsapp-connect');
        const data = await response.json();

        if (data.qrCode) {
            // Usar API gratuita para mostrar QR code como imagem
            const qrImage = document.createElement('img');
            qrImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(data.qrCode)}`;
            qrImage.alt = 'WhatsApp QR Code';
            qrImage.className = 'mx-auto';

            document.getElementById('qr-placeholder').innerHTML = '';
            document.getElementById('qr-placeholder').appendChild(qrImage);
        } else if (data.status === 'connected') {
            document.getElementById('qr-container').classList.add('d-none');
            document.getElementById('message-form').classList.remove('d-none');
            await checkStatus(); // Atualizar status
        } else {
            document.getElementById('qr-placeholder').innerHTML =
                '<p class="text-danger">Não foi possível gerar o QR Code. Por favor, tente novamente.</p>';
        }
    } catch (error) {
        console.error('Erro ao conectar:', error);
        document.getElementById('qr-placeholder').innerHTML =
            `<p class="text-danger">Erro: ${error.message}</p>`;
    }
}

// Enviar mensagem
async function sendMessage() {
    try {
        const resultContainer = document.getElementById('result-container');
        resultContainer.classList.remove('d-none');
        document.getElementById('result').textContent = 'Enviando...';

        // Obter dados do formulário
        const number = document.getElementById('number-input').value.trim();
        const message = document.getElementById('message-input').value.trim();
        const mediaInput = document.getElementById('media-input');
        const fileInput = document.getElementById('file-input');

        // Validação simples
        if (!number || !message) {
            throw new Error('Número e mensagem são obrigatórios');
        }

        // Obter o token CSRF - com fallbacks
        let token;
        const hiddenInput = document.querySelector('input[name="_token"]');
        const metaTag = document.querySelector('meta[name="csrf-token"]');

        if (hiddenInput && hiddenInput.value) {
            token = hiddenInput.value;
            console.log("Usando token do input hidden:", token);
        } else if (metaTag && metaTag.content) {
            token = metaTag.content;
            console.log("Usando token da meta tag:", token);
        } else {
            // Tenta obter do cookie como último recurso
            const encodedToken = document.cookie
                .split('; ')
                .find(row => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1];

            if (encodedToken) {
                token = decodeURIComponent(encodedToken);
                console.log("Usando token do cookie:", token);
            } else {
                throw new Error('Token CSRF não encontrado. Recarregue a página.');
            }
        }

        // Usar FormData para envio de arquivos
        const formData = new FormData();
        formData.append('_token', token);
        formData.append('number', number);
        formData.append('message', message);
        formData.append('media', fileInput.files[0]);

        // Adicionar arquivo se existir
        if (mediaInput.files && mediaInput.files[0]) {
            formData.append('media', mediaInput.files[0]);
            console.log("Arquivo adicionado:", mediaInput.files[0].name);
        }

        console.log("Enviando requisição para:", '/whatsapp-send');

        console.log([...formData.entries()]); // pra ver se a mídia está sendo anexada
        const response = await fetch('/whatsapp-send', {
            method: 'POST',
            headers: {
                // Não incluir Content-Type ao usar FormData
                // O navegador define automaticamente com o boundary correto
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData // Usar FormData ao invés de JSON.stringify
        });

        // Verificar se a resposta é válida antes de tentar processar como JSON
        if (response.ok) {
            const data = await response.json();
            resultContainer.classList.remove('bg-light', 'bg-danger', 'bg-danger-subtle');
            resultContainer.classList.add('bg-success-subtle', 'border-success');
            // Exibir resultado
            document.getElementById('result').textContent = JSON.stringify(data, null, 2);
        } else {
            console.error('Erro na requisição:', response.status);
            const errorText = await response.text();
            console.error('Resposta do servidor:', errorText);

            resultContainer.classList.remove('bg-light', 'bg-success-subtle', 'border-success');
            resultContainer.classList.add('bg-danger-subtle', 'border-danger');
            document.getElementById('result').textContent = `Erro: Status ${response.status}. ${errorText}`;
        }
    } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
        document.getElementById('result-container').classList.remove('d-none');
        document.getElementById('result-container').classList.remove('bg-light', 'bg-success-subtle', 'border-success');
        document.getElementById('result-container').classList.add('bg-danger-subtle', 'border-danger');
        document.getElementById('result').textContent = `Erro: ${error.message}`;
    }
}
