@extends('layout.app')
@section('content')
    <div class="container py-4">
        <div class="card shadow rounded-4">
            <div class="card-header bg-primary text-white rounded-top-4">
                <h1 class="h3 mb-0"><i class="fab fa-whatsapp me-2"></i>Teste de Integração WhatsApp</h1>
            </div>
            <div class="card-body p-4">
                <!-- QR Code -->
                <div id="qr-container" class="mb-4">
                    <div class="card border-2 border-primary border-dashed rounded p-4 text-center">
                        <h3 class="h5 mb-3"><i class="fas fa-qrcode me-2"></i>Escaneie o QR Code</h3>
                        <div id="qr-placeholder" class="d-flex align-items-center justify-content-center bg-light rounded" style="height: 250px;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                            <p class="text-muted ms-2">Carregando QR Code...</p>
                        </div>
                        <button id="connect-btn" class="btn btn-primary mt-3">
                            <i class="fas fa-plug me-2"></i>Conectar WhatsApp
                        </button>
                    </div>
                </div>

                <!-- Formulário de Envio -->
                <div id="message-form">
                    <h2 class="h4 mb-3 d-flex align-items-center">
                        <i class="fas fa-paper-plane me-2"></i>Enviar Mensagem
                    </h2>
                    <div class="card bg-light border-0 p-3 mb-3">
                        @csrf
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="mb-3">
                            <label for="number-input" class="form-label fw-bold">Número de Telefone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" id="number-input" class="form-control" placeholder="Ex: 11999999999">
                            </div>
                            <div class="form-text">Digite o número com código do país e DDD, sem espaços ou caracteres especiais</div>
                        </div>

                        <div class="mb-3">
                            <label for="message-input" class="form-label fw-bold">Mensagem</label>
                            <textarea id="message-input" class="form-control" rows="4" placeholder="Digite sua mensagem..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Anexar Arquivo</label>
                            <div class="input-group">
                                <input type="file" class="form-control" id="file-input">
                                <label class="input-group-text" for="file-input"><i class="fas fa-upload"></i></label>
                            </div>
                            <div class="form-text">Formatos suportados: JPG, PNG, PDF, MP3, MP4 (máx. 5MB)</div>
                        </div>

                        <div class="mb-3">
                            <label for="media-input" class="form-label fw-bold">URL da Mídia (opcional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-link"></i></span>
                                <input type="text" id="media-input" class="form-control" placeholder="https://exemplo.com/imagem.jpg">
                            </div>
                        </div>

                        <button id="send-btn" class="btn btn-success">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Mensagem
                        </button>
                    </div>
                </div>

                <div id="result-container" class="d-none mt-4">
                    <div class="card border-0 bg-light">
                        <div class="card-header bg-info bg-opacity-25">
                            <h3 class="h5 mb-0"><i class="fas fa-info-circle me-2"></i>Resultado</h3>
                        </div>
                        <div class="card-body">
                            <pre id="result" class="mb-0 bg-transparent border-0" style="white-space: pre-wrap; word-break: break-word;"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Preview do arquivo selecionado
            $('#file-input').change(function() {
                const fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $(this).next('.input-group-text').html('<i class="fas fa-check me-1"></i> ' + fileName);
                } else {
                    $(this).next('.input-group-text').html('<i class="fas fa-upload"></i>');
                }
            });

            // Função para enviar mensagem com arquivo
            $('#send-btn').click(function() {
                // Mostrar indicador de carregamento
                $(this).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enviando...');
                $(this).prop('disabled', true);

                const formData = new FormData();
                formData.append('number', $('#number-input').val());
                formData.append('message', $('#message-input').val());
                formData.append('media_url', $('#media-input').val());

                if($('#file-input')[0].files[0]) {
                    formData.append('file', $('#file-input')[0].files[0]);
                }

                $.ajax({
                    url: '/api/send-whatsapp',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    success: function(response) {
                        $('#result').text(JSON.stringify(response, null, 2));
                        $('#result-container').removeClass('d-none');
                        resetSendButton();
                    },
                    error: function(xhr) {
                        $('#result').text(JSON.stringify(xhr.responseJSON || xhr.statusText, null, 2));
                        $('#result-container').removeClass('d-none');
                        resetSendButton();
                    }
                });
            });

            function resetSendButton() {
                $('#send-btn').html('<i class="fas fa-paper-plane me-2"></i>Enviar Mensagem');
                $('#send-btn').prop('disabled', false);
            }
        });
    </script>
@endsection
