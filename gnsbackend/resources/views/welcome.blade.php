@extends('layout.app')

@section('content')
<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="section-title">Bem vindo!</h4>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="stats-card card-success">
                    <h1>0</h1>
                    <p>Enviadas</p>
                    <i class="bi bi-check-circle-fill fs-1 opacity-25 position-absolute bottom-0 end-0 mb-3 me-3"></i>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stats-card card-danger">
                    <h1>0</h1>
                    <p>Erro/Não Enviadas</p>
                    <i class="bi bi-x-circle-fill fs-1 opacity-25 position-absolute bottom-0 end-0 mb-3 me-3"></i>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stats-card card-primary">
                    <h1>0</h1>
                    <p>Total</p>
                    <i class="bi bi-clipboard-data-fill fs-1 opacity-25 position-absolute bottom-0 end-0 mb-3 me-3"></i>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <button class="btn btn-clean btn-lg">
                    <i class="bi bi-trash me-2"></i>Limpar informações e histórico
                </button>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Status das Mensagens</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Nenhum envio de mensagem registrado. Envie mensagens para visualizar estatísticas.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
{{--
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <script>
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
    </script>
</head>
<body>
<div style="text-align: center">
    <h1>GNS</h1>
    <hr>
</div>
</body>
</html>
--}}
