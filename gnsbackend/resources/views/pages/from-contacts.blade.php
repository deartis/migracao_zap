@extends('layout.app')
@section('content')
    <div class="container py-4">
        <div class="">
            <h2 class="text-center mb-4">Envio de mensagem em massa</h2>
        </div>
        <div class="card shadow-sm">
            {{-- <div class="card-header bg-light">
                <h4 class="mb-0">Enviar mensagem em massa</h4>
            </div> --}}
            <div class="card-body">
                <div class="row">
                    <!-- Card de contatos selecionados (à esquerda) -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Contatos selecionados</h5>
                            </div>
                            <div class="card-body p-0">
                                <div id="contatos-selecionados-wrapper" style="max-height: 300px; overflow-y: auto;">
                                    <ul class="list-group list-group-flush" id="contatos-selecionados-lista">
                                        <!-- Contatos selecionados serão inseridos aqui via JavaScript -->
                                        <li class="list-group-item text-center text-muted">
                                            Nenhum contato selecionado
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <span class="badge bg-success" id="contador-selecionados-card">0 selecionados</span>
                                <button class="btn btn-sm btn-outline-danger float-end" id="limpar-selecao">
                                    <i class="bi bi-trash"></i> Limpar
                                </button>
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
                                                <input type="file" class="input-file-hidden" id="arquivo">

                                                <!-- Botão personalizado com ícone -->
                                                <button type="button" class="btn btn-primary" id="custom-file-button">
                                                    <i class="bi bi-paperclip"></i>Adicionar arquivo
                                                </button>

                                                <!-- Elemento para mostrar o nome do arquivo selecionado -->
                                                <span id="file-chosen">Nenhum arquivo selecionado</span>
                                            </div>
                                        </div>
                                    </div>
                                    <textarea id="mensagem" class="form-control" rows="6" placeholder="Digite sua mensagem..."></textarea>
                                    <div class="form-text">Esta mensagem será enviada para todos os contatos selecionados.
                                    </div>
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
                        <button class="btn btn-success" id="btn-enviar-mensagem">
                            <i class="bi bi-send-fill me-1"></i> Enviar mensagem
                        </button>
                    </div>
                </div>
            </div>
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
                    <button type="button" class="btn btn-primary" id="confirmar-selecao" data-bs-dismiss="modal">Confirmar
                        seleção</button>
                </div>
            </div>
        </div>
    </div>
@endsection
