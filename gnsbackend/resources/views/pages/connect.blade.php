@extends('layout.app')

@section('content')
    <div class="container mt-5 d-flex justify-content-center">
        <div class="card shadow-lg p-4" style="width: 400px;">
            <h4 class="mb-3 text-center">Conectar WhatsApp</h4>

            <div id="qr-container" class="d-flex justify-content-center mb-3">
                <div class="text-muted">Aguardando QR Code...</div>
            </div>

            <div id="status" class="text-center mt-3"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const token = 'SEU_TOKEN_AQUI'; // Aqui você deve usar o token do usuário logado dinamicamente
            const headers = {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            };

            async function fetchQRCode() {
                try {
                    const response = await fetch('http://localhost:3000/start-whatsapp', { headers });
                    const data = await response.json();

                    const qrContainer = document.getElementById('qr-container');
                    const status = document.getElementById('status');

                    if (data.qrCode) {
                        const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(data.qrCode)}&size=250x250`;
                        qrContainer.innerHTML = `<img src="${qrCodeUrl}" alt="QR Code do WhatsApp" class="img-fluid">`;
                        status.innerHTML = `<span class="text-info">Escaneie o QR Code com seu WhatsApp</span>`;

                        // Verifica novamente após alguns segundos para ver se conectou
                        setTimeout(fetchQRCode, 5000);
                    } else if (data.status === 'connected') {
                        qrContainer.innerHTML = `<p class="text-success fw-bold">WhatsApp conectado: ${data.phoneNumber}</p>`;
                        status.innerHTML = `<i class="bi bi-check-circle-fill text-success"></i> Conectado com sucesso!`;
                    } else {
                        qrContainer.innerHTML = `<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div>`;
                        status.innerHTML = `<span class="text-warning">Aguardando QR code... tentando novamente</span>`;
                        setTimeout(fetchQRCode, 3000);
                    }
                } catch (error) {
                    console.error('Erro ao buscar QR Code:', error);
                    document.getElementById('status').innerHTML = `<span class="text-danger">Erro ao carregar QR Code</span>`;
                    setTimeout(fetchQRCode, 5000);
                }
            }

            fetchQRCode();
        });
    </script>
@endsection
