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
