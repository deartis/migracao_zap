@extends('layout.app')
@section('content')
    <div class="container py-4">
        <div class="">
            <h2 class="text-center mb-4">Envio de mensagem individual</h2>
        </div>
        <div class="card shadow-sm">
            {{-- <div class="card-header bg-light">
                <h4 class="mb-0">Enviar mensagem em massa</h4>
            </div> --}}
            <div id="resultado"></div>
            <form id="form-msg_unica" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <!-- Card de contatos selecionados (à esquerda) -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    {{--<h5 class="mb-0">Contatos</h5>--}}
                                    <input type="text" placeholder="Digite ou cliqueem um contato" id="contato" name="contato" class="form-group text-center">
                                </div>
                                <div class="card-body p-0">
                                    <div id="contatos-selecionados-wrapper"
                                         style="max-height: 300px; overflow-y: auto;">
                                        <ul class="list-group list-group-flush" id="contatos-selecionados-lista">
                                            <!-- Contatos selecionados serão inseridos aqui via JavaScript -->
                                            <li class="list-group-item text-center text-muted">
                                                Nenhum contato
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Card da mensagem (à direita) -->
                        <div class="col-md-8 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Mensagem</h5>
                                </div>
                                <div class="card-body">

                                    <div class="mb-3">
                                        <div class="row">
                                            <div class="">
                                                <!-- Botão para importar arquivo -->
                                                <div class="mb-3">
                                                    <!-- Input file original (oculto) -->
                                                    <input type="file" class="input-file-hidden form-control" name="arquivo" id="arquivo">

                                                    <!-- Botão personalizado com ícone -->
                                                    <button type="button" class="btn btn-primary"
                                                            id="custom-file-button">
                                                        <i class="bi bi-paperclip"></i>Adicionar arquivo
                                                    </button>

                                                    <!-- Elemento para mostrar o nome do arquivo selecionado -->
                                                    <span id="file-chosen"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <textarea id="mensagem_unica" class="form-control" rows="6"
                                                  placeholder="Digite sua mensagem..."></textarea>
                                        {{--<div class="form-text">Esta mensagem será enviada para todos os contatos selecionados.
                                        </div>--}}
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Botão para importar contatos da API -->
                            <div class="">
                                <button class="btn btn-primary" id="btn-importar-contatos" data-bs-toggle="modal"
                                        data-bs-target="#modalContatos">
                                    <i class="bi bi-whatsapp me-1"></i>Carregar contatos
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-success" {{--id="btn-enviar-mensagem"--}}>
                                <i class="bi bi-send-fill me-1"></i> Enviar mensagem
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para seleção de contatos -->
    <div class="modal fade" id="modalContatos" tabindex="-1" aria-labelledby="modalContatosLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalContatosLabel">Selecionar Contatos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Barra de pesquisa -->
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="pesquisar-contatos"
                               placeholder="Pesquisar contatos...">
                    </div>

                    <!-- Cabeçalho da lista de contatos -->
                    <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="selecionar-todos">
                            <label class="form-check-label fw-bold" for="selecionar-todos">
                                Selecionar todos os contatos
                            </label>
                        </div>
                        <span class="badge bg-primary" id="contador-selecionados-modal">0 selecionados</span>
                    </div>

                    <!-- Lista de contatos -->
                    <div id="contatos-wrapper" style="max-height: 50vh; overflow-y: auto;">
                        <ul class="list-group list-group-flush" id="contatos-checkboxes">
                            <!-- Contatos serão inseridos aqui via JavaScript -->
                        </ul>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmar-selecao" data-bs-dismiss="modal">
                        Confirmar
                        seleção
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('form-msg_unica').addEventListener('submit', function (e) {
            e.preventDefault();

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const contato = document.getElementById('contato').value;
            const mensagem = document.getElementById('mensagem_unica').value;
            const arquivoInput = document.getElementById('arquivo');
            const arquivo = arquivoInput.files[0];

            if (arquivo) {
                const reader = new FileReader();
                reader.onload = function () {
                    const base64 = reader.result.split(',')[1]; // Remove o prefixo data:...;base64,
                    const dados = {
                        contato: contato,
                        mensagem: mensagem,
                        arquivo: {
                            nome: arquivo.name,
                            mimetype: arquivo.type,
                            base64: base64
                        }
                    };

                    axios.post('/page/single-contact/send', dados, {
                        headers: {
                            'X-CSRF-TOKEN': token
                        }
                    })
                        .then(response => {
                            console.log(response.data);
                            document.getElementById('resultado').innerText = 'Mensagem enviada com sucesso';
                        })
                        .catch(error => {
                            console.log(error.response.data);
                            document.getElementById('resultado').innerText = 'Erro ao enviar a mensagem';
                        });
                };
                reader.readAsDataURL(arquivo);
            } else {
                const dados = {
                    contato: contato,
                    mensagem: mensagem
                };

                axios.post('/page/single-contact/send', dados, {
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                })
                    .then(response => {
                        console.log(response.data);
                        document.getElementById('resultado').innerText = 'Mensagem enviada com sucesso';
                    })
                    .catch(error => {
                        console.log(error.response.data);
                        document.getElementById('resultado').innerText = 'Erro ao enviar a mensagem';
                    });
            }
        });

        /*document.getElementById('form-msg_unica').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = document.getElementById('form-msg_unica');
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const formData = new FormData(form);

            axios.post('/page/single-contact/send', formData, {
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then(response => {
                    console.log(response.data);
                    document.getElementById('resultado').innerText = 'Mensagem enviada com sucesso!';
                })
                .catch(error => {
                    console.error(error.response?.data || error);
                    document.getElementById('resultado').innerText = 'Erro ao enviar a mensagem.';
                });
        });*/
    </script>
@endsection

{{--<div class="">
        <div class="p-4">

            @bloqueado
            <div class="alert alert-danger text-center">
                Sua conta está bloqueada. Por favor, entre em contato com o suporte.
            </div>

            @else

                <h1 class="text-center mb-4">
                    <i class="fab fa-whatsapp text-success me-2"></i>Mensagem Individual
                </h1>

                <div class="mb-5 d-none">
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
                            <label for="file-input" class="form-label">Arquivo</label>
                            <input type="file" id="file-input" class="form-control">
                            <div class="form-text">Formatos suportados: PDF, imagens, áudio (máx. 5MB)</div>
                            <div class="alert alert-info mt-4 mb-4" >
                                <p>Temporariamente o envio de vídeo está inativo!</p>
                            </div>
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
            @endbloqueado


        </div>
    </div>--}}
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
