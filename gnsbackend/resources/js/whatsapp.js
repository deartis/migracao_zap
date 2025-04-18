async function startWhatsApp() {
    try {
        const response = await fetch('/api/whatsapp/start', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'include' // Para enviar cookies de autenticação
        });

        const data = await response.json();

        if (data.qrCode) {
            // Exibe o QR code para o usuário escanear
            document.getElementById('qrcode').innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(data.qrCode)}" alt="QR Code">`;
        } else if (data.status === 'connected') {
            // Já está conectado
            document.getElementById('status').textContent = `Conectado com o número: ${data.phoneNumber}`;
        }
    } catch (error) {
        console.error('Erro ao iniciar WhatsApp:', error);
    }
}

// Verificar status periodicamente
setInterval(async () => {
    try {
        const response = await fetch('/api/whatsapp/status', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
            credentials: 'include'
        });

        const data = await response.json();
        document.getElementById('status').textContent = `Status: ${data.status}`;

        if (data.status === 'connected') {
            document.getElementById('qrcode').innerHTML = '';
            document.getElementById('phone').textContent = `Número: ${data.phoneNumber}`;
        }
    } catch (error) {
        console.error('Erro ao verificar status:', error);
    }
}, 5000);
