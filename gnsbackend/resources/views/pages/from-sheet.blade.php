@extends('layout.app')
@section('styles')
    <style>

    </style>
@endsection
@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="text-center mb-4">Envio em Massa</h1>

                <!-- Barra de progresso -->
                <div class="progress mb-4" style="height: 10px;">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                        style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <!-- 1️⃣ Importação de Arquivo XLSX -->
                <div id="step1" class="step-container step-active bg-light">
                    <div class="step-header">
                        <div class="step-number">1</div>
                        <h3>Importar Dados</h3>
                    </div>

                    <div id="fileDropArea" onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-file-earmark-excel fs-1 text-success mb-3"></i>
                        <h5>Clique aqui para buscar um arquivo</h5>
                        <p class="text-muted">Formatos suportados: .xlsx, .xls, .csv</p>
                        <input type="file" id="fileInput" accept=".xlsx, .xls, .csv" />
                    </div>

                    <div id="fileDetails" class="alert alert-success mt-3" style="display: none;">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div>
                                <span id="fileName"></span>
                                <div id="fileSize" class="small"></div>
                            </div>
                            <button type="button" class="btn-close ms-auto" onclick="resetFileInput()"></button>
                        </div>
                    </div>

                    <div id="tablePreview" class="preview-container mt-3" style="display: none;">
                        <h5>Prévia dos dados:</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" id="previewTable">
                                <!-- Tabela de prévia será inserida aqui -->
                            </table>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button id="btnProximoPasso1" class="btn btn-primary" disabled>
                            Próximo <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- 2️⃣ Seleção de Colunas -->
                <div id="step2" class="step-container step-inactive bg-light" style="display: none;">
                    <div class="step-header">
                        <div class="step-number">2</div>
                        <h3>Selecionar Colunas</h3>
                    </div>

                    <p class="mb-3">Selecione as colunas que você deseja usar como placeholders na sua mensagem:</p>

                    <form id="formSelecaoColunas" class="row g-3">
                        <!-- As colunas serão adicionadas aqui dinamicamente -->
                    </form>

                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" id="btnVoltarPasso2" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </button>
                        <button type="button" id="btnConfirmarSelecao" class="btn btn-primary">
                            Próximo <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- 3️⃣ Editor de Mensagem -->
                <div id="step3" class="step-container step-inactive bg-light" style="display: none;">
                    <div class="step-header">
                        <div class="step-number">3</div>
                        <h3>Compor Mensagem</h3>
                    </div>

                    <div class="mb-3">
                        <div class="mb-3">
                            <!-- Input file original (oculto) -->
                            <input type="file" class="input-file-hidden form-control" name="arquivo_msg_massa"
                                id="arquivo_msg_massa">

                            <!-- Botão personalizado com ícone -->
                            <button type="button" class="btn btn-primary w-auto" id="custom-file-button_msg_massa">
                                <i class="bi bi-paperclip"></i>Adicionar arquivo
                            </button>

                            <!-- Elemento para mostrar o nome do arquivo selecionado -->
                            <span id="file-chosen"></span>
                        </div>
                        <textarea id="messageTextarea" class="form-control" rows="8"
                            placeholder="Digite sua mensagem aqui. Use os placeholders abaixo para personalizar para cada destinatário."></textarea>
                    </div>

                    <div class="mb-3">
                        <button class="btn btn-primary" id="btnCarregarTemplate">Carregar Mensagem</button>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Placeholders Disponíveis</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text small text-muted mb-2">Clique em um placeholder para adicioná-lo à sua
                                mensagem:</p>
                            <div id="botoesPlaceholders" class="d-flex flex-wrap"></div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Prévia da Mensagem</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-floating mb-3 d-none">
                                <select class="form-select" id="previewSelector">
                                    <option selected>Selecione um registro para visualizar</option>
                                    <!-- Opções serão adicionadas dinamicamente -->
                                </select>
                                <label for="previewSelector">Visualizar para:</label>
                            </div>
                            <div id="messagePreview" class="border p-3 rounded bg-white">
                                <!-- Prévia da mensagem será exibida aqui -->
                                <p class="text-muted text-center">A prévia da mensagem será exibida aqui.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" id="btnVoltarPasso3" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </button>
                        <button type="button" id="btnEnviarMensagens" class="btn btn-success">
                            <i class="bi bi-send-fill"></i> Enviar Mensagens
                        </button>
                    </div>
                </div>

                <!-- Modal de Confirmação -->
                <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-dark">
                                <h5 class="modal-title">Confirmar Envio</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Você está prestes a enviar mensagens para <strong id="totalRecipients">0</strong>
                                    destinatários.</p>
                                <p>Deseja continuar?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar
                                </button>
                                <button type="button" class="btn btn-success" id="btnConfirmarEnvio">Confirmar Envio
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="modalEnvio" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
                    data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content text-center">
                            <div class="modal-body">
                                <h5 class="modal-title mb-3">Enviando mensagens...</h5>
                                <p id="progressoTexto">0 de 0</p>
                                <div class="progress" style="height: 25px; position: relative;">
                                    <div id="barraProgresso"
                                        class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                        style="width: 0%;">
                                        0%
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        id="btnFecharModal">Fechar</button>
                                    <p id="msgBackground" class="text-muted mt-2" style="display: none;">
                                        As mensagens continuam sendo enviadas em segundo plano.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Modal -->
                {{-- <div class="modal fade" id="modalEnvio" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
                    data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content text-center">
                            <div class="modal-body">
                                <h5 class="modal-title mb-3">Enviando mensagens...</h5>
                                <p id="progressoTexto">0 de 0</p>
                                <div class="progress" style="height: 25px;">
                                    <div id="barraProgresso"
                                        class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                        style="width: 0%;">
                                        0%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}

            </div>
        </div>
    </div>
@endsection

{{-- @section('scripts')
    <script>
        function carregaTemplate() {
            fetch('/get-tpl')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('messageTextarea').value = data.template;
                });
        }
    </script>
@endsection --}}
