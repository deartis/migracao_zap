@extends('layout.app')
@section('content')
    <div class="">
        <div class="p-4">
            <h1 class="text-center mb-4">
                <i class="fab fa-whatsapp text-success me-2"></i>Teste de Integração WhatsApp
            </h1>

            <div class="mb-5">
                <h2 class="h4 mb-3">Status da Conexão</h2>
                <div class="d-flex align-items-center mb-3 gap-3">
                    <span>Status:</span>
                    <span id="connection-status" class="badge bg-secondary">Carregando...</span>
                    <span id="phone-number" class="fst-italic"></span>
                </div>
                <button id="connect-btn" class="btn btn-primary">
                    <i class="fas fa-plug me-2"></i>Conectar WhatsApp
                </button>
            </div>

            <div id="qr-container" class="d-none mb-4 border border-2 border-dashed rounded p-4 text-center">
                <h3 class="h5 mb-3">Escaneie o QR Code</h3>
                <div id="qr-placeholder" class="d-flex align-items-center justify-content-center" style="height: 250px;">
                    <p class="text-muted">Carregando QR Code...</p>
                </div>
            </div>

            <div id="message-form" class="d-none">
                <h2 class="h4 mb-3">Enviar Mensagem</h2>
                <div class="mb-3">
                    @csrf
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="mb-3">
                        <label for="number-input" class="form-label">Número de Telefone</label>
                        <input type="text" id="number-input" class="form-control" placeholder="Ex: 11999999999">
                        <div class="form-text">Digite o número com código do país e DDD, sem espaços ou caracteres especiais</div>
                    </div>
                    <div class="mb-3">
                        <label for="message-input" class="form-label">Mensagem</label>
                        <textarea id="message-input" class="form-control" rows="4" placeholder="Digite sua mensagem..."></textarea>
                    </div>

                    <!-- Novo campo para upload de arquivos -->
                    <div class="mb-3">
                        <label for="file-input" class="form-label">Arquivo (opcional)</label>
                        <input type="file" id="file-input" class="form-control">
                        <div class="form-text">Formatos suportados: PDF, imagens, áudio, vídeo (máx. 5MB)</div>
                    </div>

                    <div class="mb-3 d-none">
                        <label for="media-input" class="form-label">URL da Mídia (opcional)</label>
                        <input type="text" id="media-input" class="form-control" placeholder="https://exemplo.com/imagem.jpg">
                    </div>

                    <button id="send-btn" class="btn btn-success w-100">
                        <i class="fas fa-paper-plane me-2"></i>Enviar Mensagem
                    </button>
                </div>
            </div>

            <div id="result-container" class="d-none mt-4 p-3 bg-light border rounded">
                <h3 class="h5 mb-2">Resultado</h3>
                <pre id="result" class="mb-0" style="white-space: pre-wrap; word-break: break-word;"></pre>
            </div>
        </div>
    </div>


@endsection

{{--
@extends('layout.app')
@section('content')
    <div class="">
        <div class="p-4">

            <div id="message-form" class="">
                <h2 class="h4 mb-3">Enviar Mensagem</h2>
                <div class="mb-3">
                    @csrf
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="mb-3">
                        <label for="number-input" class="form-label">Número de Telefone</label>
                        <input type="text" id="number-input" class="form-control" placeholder="Ex: 11999999999">
                        <div class="form-text">Digite o número com código do país e DDD, sem espaços ou caracteres especiais</div>
                    </div>
                    <div class="mb-3">
                        <label for="message-input" class="form-label">Mensagem</label>
                        <textarea id="message-input" class="form-control" rows="4" placeholder="Digite sua mensagem..."></textarea>
                    </div>

                    <!-- Novo campo para upload de arquivos -->
                    <div class="mb-3">
                        <label for="file-input" class="form-label">Arquivo</label>
                        <input type="file" id="file-input" class="form-control">
                        <div class="form-text">Formatos suportados: PDF, imagens, áudio, vídeo (máx. 5MB)</div>
                    </div>

                    <div class="mb-3 d-none">
                        <label for="media-input" class="form-label">URL da Mídia (opcional)</label>
                        <input type="text" id="media-input" class="form-control" placeholder="https://exemplo.com/imagem.jpg">
                    </div>

                    <button id="send-btn" class="btn btn-success w-100">
                        <i class="fas fa-paper-plane me-2"></i>Enviar Mensagem
                    </button>
                </div>
            </div>

            <div id="result-container" class="d-none mt-4 p-3 bg-light border rounded">
                <h3 class="h5 mb-2">Resultado</h3>
                <pre id="result" class="mb-0" style="white-space: pre-wrap; word-break: break-word;"></pre>
            </div>
        </div>
    </div>


@endsection
--}}
