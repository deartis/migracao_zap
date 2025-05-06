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
                        <p class="text-muted">Formatos suportados: .xlsx, .xls</p>
                        <input type="file" id="fileInput" accept=".xlsx, .xls" onchange="handleFileSelect(event)"/>
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
                            <div class="form-floating mb-3">
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


    <!-- Biblioteca XLSX -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"
            integrity="sha512-r22gChDnGvBylk90+2e/ycr3RVrDi8DIOkIGNhJlKfuyQM4tIRAI062MaV8sfjQKYVGjOBaZBOA87z+IhZE9DA=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        let previewData = []; // Dados importados do Excel
        let colunasDisponiveis = []; // Nome das colunas

        // Atualiza a barra de progresso
        function updateProgress(percent) {
            const bar = document.getElementById('progressBar');
            bar.style.width = percent + '%';
            bar.setAttribute('aria-valuenow', percent);
        }

        // Troca de passos
        function goToStep(fromStep, toStep, progressPercent) {
            document.getElementById(fromStep).style.display = 'none';
            document.getElementById(fromStep).classList.remove('step-active');
            document.getElementById(fromStep).classList.add('step-completed');

            document.getElementById(toStep).style.display = 'block';
            document.getElementById(toStep).classList.remove('step-inactive');
            document.getElementById(toStep).classList.add('step-active');

            updateProgress(progressPercent);
        }

        // Função de leitura do arquivo Excel
        function handleFileSelect(evt) {
            const file = evt.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                const data = e.target.result;
                const workbook = XLSX.read(data, {type: 'binary'});
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];

                // Converter para JSON mantendo cabeçalhos
                const jsonData = XLSX.utils.sheet_to_json(firstSheet, {header: 1});

                if (jsonData.length < 2) {
                    alert('O arquivo precisa ter pelo menos 1 linha de dados.');
                    return;
                }

                // Obter cabeçalhos (primeira linha)
                colunasDisponiveis = jsonData[0];

                // Filtrar linhas vazias antes de atribuir a previewData
                previewData = jsonData.slice(1).filter(row => {
                    // Verificar se a linha contém pelo menos um valor não vazio
                    return row.some(cell => cell !== null && cell !== undefined && cell.toString().trim() !== '');
                });

                console.log("Cabeçalhos:", colunasDisponiveis);
                console.log("Dados filtrados:", previewData);
                console.log("Total de registros válidos:", previewData.length);

                showTablePreview([colunasDisponiveis, ...previewData]);

                document.getElementById('fileName').innerText = file.name;
                document.getElementById('fileSize').innerText = (file.size / 1024).toFixed(2) + ' KB';
                document.getElementById('fileDetails').style.display = 'block';
                document.getElementById('tablePreview').style.display = 'block';

                document.getElementById('btnProximoPasso1').disabled = false;
            };
            reader.readAsBinaryString(file);
        }

        function resetFileInput() {
            document.getElementById('fileInput').value = '';
            document.getElementById('fileDetails').style.display = 'none';
            document.getElementById('tablePreview').style.display = 'none';
            document.getElementById('btnProximoPasso1').disabled = true;
        }

        function showTablePreview(data) {
            const table = document.getElementById('previewTable');
            table.innerHTML = '';

            data.forEach((row, index) => {
                const tr = document.createElement('tr');

                // Destacar linhas vazias (para depuração)
                const isEmpty = row.every(cell => cell === null || cell === undefined || cell.toString().trim() === '');
                if (isEmpty && index > 0) {
                    tr.classList.add('table-secondary');
                }

                // Se for a primeira linha (cabeçalho), adicionar índice da linha para ajudar na depuração
                if (index === 0) {
                    const th = document.createElement('th');
                    th.textContent = "#";
                    tr.appendChild(th);
                } else {
                    const td = document.createElement('td');
                    td.textContent = index;
                    tr.appendChild(td);
                }

                // Adicionar células
                row.forEach(cell => {
                    const tag = index === 0 ? 'th' : 'td';
                    const cellElem = document.createElement(tag);
                    cellElem.textContent = cell ?? '';
                    tr.appendChild(cellElem);
                });

                table.appendChild(tr);
            });
        }

        // Cria CHECKBOX (input + datalist) para cada coluna
        function gerarCamposSelecao() {
            const form = document.getElementById('formSelecaoColunas');
            form.innerHTML = '';

            colunasDisponiveis.forEach(coluna => {
                const div = document.createElement('div');
                div.className = 'form-check col-md-4';

                div.innerHTML = `
            <input class="form-check-input" type="checkbox" value="${coluna}" id="coluna-${coluna}">
            <label class="form-check-label fw-bold" for="coluna-${coluna}">
                ${coluna}
            </label>
        `;

                form.appendChild(div);
            });
        }

        // Primeiro debugar o problema para ver o que está acontecendo
        console.log('Depurando geração de botões');

        // 1. Corrigir botões de placeholders
        function gerarBotoesPlaceholders(colunasSelecionadas) {
            const container = document.getElementById('botoesPlaceholders');
            container.innerHTML = '';

            console.log('Colunas selecionadas:', colunasSelecionadas);

            colunasSelecionadas.forEach(coluna => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline-primary btn-sm me-2 mb-2';
                // Armazenar apenas o nome da coluna, sem o símbolo +
                btn.dataset.placeholder = coluna;
                // Usar textContent para evitar interpretação HTML
                btn.textContent = '{{' + coluna + '}}';

                // IMPORTANTE: Função de clique completamente reescrita
                btn.onclick = function () {
                    console.log('Clique em placeholder:', coluna);
                    // Usar uma string literal direta, sem this.dataset
                    const placeholder = '{{' + coluna + '}}';
                    insertAtCursor(document.getElementById('messageTextarea'), placeholder);
                };

                container.appendChild(btn);
            });
        }

        // Insere texto no cursor do textarea
        function insertAtCursor(textarea, text) {
            console.log('Inserindo texto:', text);
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const value = textarea.value;

            textarea.value = value.substring(0, start) + text + value.substring(end);
            textarea.selectionStart = textarea.selectionEnd = start + text.length;
            textarea.focus();
        }

        // Navegação dos passos
        document.getElementById('btnProximoPasso1').addEventListener('click', function () {
            gerarCamposSelecao();
            goToStep('step1', 'step2', 66);
        });

        document.getElementById('btnVoltarPasso2').addEventListener('click', function () {
            goToStep('step2', 'step1', 33);
        });

        // Atualização na exibição do contador de destinatários
        document.getElementById('btnConfirmarSelecao').addEventListener('click', function () {
            const checkboxes = document.querySelectorAll('#formSelecaoColunas input[type="checkbox"]');
            const colunasSelecionadas = [];

            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    colunasSelecionadas.push(checkbox.value);
                }
            });

            if (colunasSelecionadas.length === 0) {
                alert('Selecione pelo menos uma coluna.');
                return;
            }

            gerarBotoesPlaceholders(colunasSelecionadas);
            gerarPrevia(colunasSelecionadas);

            goToStep('step2', 'step3', 100);

            // Atualizar contagem com informações adicionais
            document.getElementById('totalRecipients').innerText = previewData.length;

            // Adicionar informação para depuração (opcional)
            const debugInfo = document.createElement('small');
            debugInfo.className = 'text-muted d-block';
            debugInfo.innerHTML = `<i class="bi bi-info-circle"></i> Linhas filtradas: ${previewData.length} válidas de ${jsonData.length - 1} total`;
            document.getElementById('totalRecipients').parentNode.appendChild(debugInfo);
        });


        document.getElementById('btnVoltarPasso3').addEventListener('click', function () {
            goToStep('step3', 'step2', 66);
        });

        document.getElementById('btnEnviarMensagens').addEventListener('click', function () {
            const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            modal.show();
        });

        // Substitua a função de confirmação de envio por esta:
        document.getElementById('btnConfirmarEnvio').addEventListener('click', async function () {
            const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
            modal.hide();

            // Mostrar loading
            const btnEnviar = document.getElementById('btnEnviarMensagens');
            const originalText = btnEnviar.innerHTML;
            btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';
            btnEnviar.disabled = true;

            try {
                // Obter colunas selecionadas CORRETAMENTE
                const checkboxes = document.querySelectorAll('#formSelecaoColunas input[type="checkbox"]:checked');
                const selectedColumns = Array.from(checkboxes).map(checkbox => checkbox.value);

                // Criar FormData corretamente
                const formData = new FormData();
                formData.append('file', document.getElementById('fileInput').files[0]);
                formData.append('message', document.getElementById('messageTextarea').value);

                // Adicionar cada coluna selecionada individualmente
                selectedColumns.forEach((column, index) => {
                    formData.append(`selected_columns[${index}]`, column);
                });

                // Enviar para o backend
                const response = await fetch('/whatsapp-send-bulk', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) throw new Error(data.message || 'Erro ao enviar mensagens');

                // Sucesso
                alert(`✅ ${data.message}\nLote: ${data.batch_id}`);
                //location.reload();

            } catch (error) {
                console.error('Erro:', error);
                alert('❌ Erro ao enviar mensagens: ' + error.message);
            } finally {
                btnEnviar.innerHTML = originalText;
                btnEnviar.disabled = false;
            }
        });
    </script>
@endsection


