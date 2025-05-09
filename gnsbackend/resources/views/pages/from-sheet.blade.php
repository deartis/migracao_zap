@extends('layout.app')
@section('styles')
    <style>
        .step-container {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .step-active {
            box-shadow: 0 0 15px rgba(0, 123, 255, 0.2);
        }

        .step-inactive {
            opacity: 0.7;
        }

        .step-completed {
            border-left: 5px solid #28a745;
        }

        .step-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #f8f9fa;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }

        .step-active .step-number {
            background-color: #0d6efd;
            color: white;
        }

        .step-completed .step-number {
            background-color: #28a745;
            color: white;
        }

        .placeholder-badge {
            cursor: pointer;
            margin: 5px;
            transition: all 0.2s;
        }

        .placeholder-badge:hover {
            transform: translateY(-2px);
        }

        #fileDropArea {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        #fileDropArea:hover {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.05);
        }

        #fileInput {
            display: none;
        }

        .preview-container {
            max-height: 250px;
            overflow-y: auto;
            overflow-x: scroll;
            margin-top: 15px;
        }
    </style>
@endsection
@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="text-center mb-4">Envio em Massa</h1>

                <!-- Barra de progresso -->
                <div class="progress mb-4" style="height: 10px;">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                         role="progressbar" style="width: 33%;" aria-valuenow="33" aria-valuemin="0"
                         aria-valuemax="100"></div>
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
                        <input type="file" id="fileInput" accept=".xlsx, .xls, .csv" onchange="handleFileSelect(event)"/>
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
                        <label for="messageTextarea" class="form-label fw-bold">Mensagem em Massa:</label>
                        <textarea id="messageTextarea" class="form-control" rows="8"
                                  placeholder="Digite sua mensagem aqui. Use os placeholders abaixo para personalizar para cada destinatário."></textarea>
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
                            <div class="modal-header bg-warning text-dark">
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
                                <button type="button" class="btn btn-warning" id="btnConfirmarEnvio">Confirmar Envio
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection



