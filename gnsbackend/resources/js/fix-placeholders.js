// Arquivo processador-excel-whatsapp.js

document.addEventListener('DOMContentLoaded', function () {
    // Variáveis globais
    let previewData = []; // Dados importados do Excel
    let colunasDisponiveis = []; // Nome das colunas
    let jsonOriginalData = []; // Dados completos em formato JSON (linha -> objeto)
    let colunasSelecionadas = [];

    // Referencias HTML
    const fileInput = document.getElementById('fileInput');
    const progressBar = document.getElementById('progressBar');
    const previewTable = document.getElementById('previewTable');
    const messageTextarea = document.getElementById('messageTextarea');
    const messagePreview = document.getElementById('messagePreview');
    const totalRecipients = document.getElementById('totalRecipients');
    const botoesPlaceholders = document.getElementById('botoesPlaceholders');
    const fileInputJoinMsg = document.getElementById('arquivo_msg_massa');
    const btnCustomFileMsg = document.getElementById('custom-file-button_msg_massa');
    const fileChosen = document.getElementById('file-chosen');
    const arquivoMsgMassaList = fileInputJoinMsg.value;
    const btn = document.getElementById('btnEnviarMensagens');

    btnCustomFileMsg.addEventListener('click', function () {
        fileInputJoinMsg.click();
    });

    let fileMsg = null;
    fileInputJoinMsg.addEventListener('change', function () {
        if (fileInputJoinMsg.value) {
            // Extrai apenas o nome do arquivo do caminho completo
            fileChosen.textContent = fileInputJoinMsg.value.split('\\').pop();
            fileMsg = fileInputJoinMsg.files[0]; // Armazena o arquivo selecionado

        } else {
            fileChosen.textContent = 'Nenhum arquivo selecionado';
        }
    });


    /*

    * */

    // Event listeners
    if (fileInput) {
        fileInput.addEventListener('change', handleFileSelect);
    }

    document.getElementById('btnProximoPasso1')?.addEventListener('click', function () {
        gerarCamposSelecao();
        goToStep('step1', 'step2', 66);
    });

    document.getElementById('btnVoltarPasso2')?.addEventListener('click', function () {
        goToStep('step2', 'step1', 33);
    });

    document.getElementById('btnConfirmarSelecao')?.addEventListener('click', function () {
        const checkboxes = document.querySelectorAll('#formSelecaoColunas input[type="checkbox"]');
        colunasSelecionadas = [];

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                colunasSelecionadas.push(checkbox.value);
            }
        });

        if (colunasSelecionadas.length === 0) {
            alert('Selecione pelo menos uma coluna.');
            return;
        }

        // Detect important columns (nome e whatsapp)
        const columnMapping = detectImportantColumns(colunasSelecionadas);

        gerarBotoesPlaceholders(colunasSelecionadas);
        gerarPrevia(colunasSelecionadas);

        goToStep('step2', 'step3', 100);

        // Atualizar contagem
        if (totalRecipients) {
            totalRecipients.innerText = previewData.length;
        }
    });

    document.getElementById('btnVoltarPasso3')?.addEventListener('click', function () {
        goToStep('step3', 'step2', 66);
    });

    document.getElementById('btnEnviarMensagens')?.addEventListener('click', function () {
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        modal.show();
    });

    document.getElementById('btnCarregarTemplate')?.addEventListener('click', function () {
        fetch('/get-tpl')
            .then(response => response.json())
            .then(data => {
                const mensagem = data.template;
                if (mensagem) {
                    // Preenche o textarea com o template
                    document.getElementById('messageTextarea').value = mensagem;

                    // Atualiza visualização com o conteúdo puro (sem substituições)
                    const preview = document.getElementById('messagePreview');
                    if (preview) {
                        preview.innerHTML = '<p>' + mensagem.replace(/\n/g, '<br>') + '</p>';
                    }

                    // ✅ Chama a função de gerar prévia formatada com os dados reais
                    if (typeof gerarPrevia === 'function' && colunasSelecionadas) {
                        gerarPrevia(colunasSelecionadas);
                    }
                } else {
                    alert('Não foi encontrado nenhuma mensagem padrão');
                }
            });
    });

    document.getElementById('btnConfirmarEnvio')?.addEventListener('click', async function () {
        // Verifica se o arquivo já foi selecionado
        console.log('Arquivo selecionado:', fileMsg);
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
        modal.hide();

        // Mostrar loading
        const btnEnviar = document.getElementById('btnEnviarMensagens');
        const originalText = btnEnviar.innerHTML;
        btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';
        btnEnviar.disabled = true;

        try {
            // Obter colunas selecionadas
            const checkboxes = document.querySelectorAll('#formSelecaoColunas input[type="checkbox"]:checked');
            const selectedColumns = Array.from(checkboxes).map(checkbox => checkbox.value);
            //const btn = document.getElementById('btnEnviarMensagens');

            // Detectar colunas importantes
            const columnMapping = detectImportantColumns(selectedColumns);

            // Criar JSON estruturado com os dados
            const contacts = createStructuredJson(selectedColumns, columnMapping);

            // Função para substituir placeholders na mensagem
            function personalizeMessage(template, contact) {
                // Substitui todos os placeholders que existem no contato
                let personalized = template.replace(/{{(.*?)}}/g, (match, key) => {
                    key = key.trim(); // Remove espaços extras dentro de {{   }}
                    return contact.hasOwnProperty(key) && contact[key] != null ? contact[key] : '';
                });

                return personalized;
            }


            // Gerar o array de contatos já com as mensagens personalizadas
            const personalizedContacts = contacts.map(contact => {
                return {
                    ...contact,
                    message: personalizeMessage(messageTextarea.value, contact)
                };
            });


            // Criar dados para envio
            const jsonData = {
                contacts: personalizedContacts,
                template: messageTextarea.value
            };

            const formData = new FormData();
            formData.append('data', JSON.stringify(jsonData));

            // Verifica se o arquivo foi selecionado
            if (fileMsg) {
                formData.append('arquivo', fileMsg);
            }

            // Enviar para o backend usando fetch API
            const response = await fetch('/envia-mensagem-em-massa-lista', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok) throw new Error(data.message || 'Erro ao enviar mensagens');

            // Resetar progresso antes de exibir o modal
            await fetch('/reseta-progresso', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            // Agora mostra o modal e começa a 
            
            mostrarModal();


        } catch (error) {
            console.error('Erro:', error);
            alert('❌ Erro ao enviar mensagens: ' + error.message);
        } finally {
            btnEnviar.innerHTML = originalText;
            btnEnviar.disabled = false;
        }
    });

    //Função para mostrar o progresso das mensagens enviadas
    function mostrarModal() {
        const modal = new bootstrap.Modal(document.getElementById('modalEnvio'));
        //const btn = document.getElementById('btnEnviarMensagens');
        modal.show();

        document.getElementById('modalEnvio').style.display = 'block';
        const intervalo = setInterval(() => {
            fetch('/envio-progresso') // rota que retorna JSON com progresso
                .then(res => res.json())
                .then(data => {
                    const { total, enviadas, status } = data;
                    const porcentagem = total > 0 ? Math.round((enviadas / total) * 100) : 0;
                    //const porcentagem = Math.round((enviadas / total) * 100);
                    document.getElementById('progressoTexto').innerText = `${enviadas}/${total}`;
                    const barra = document.getElementById('barraProgresso');
                    barra.style.width = `${porcentagem}%`;
                    barra.innerText = `${porcentagem}%`;
                    document.getElementById('barraProgresso').value = porcentagem;

                    if (status === 'finalizado') {
                        clearInterval(intervalo);
                        setTimeout(() => {
                            modal.hide();
                            // Chamar backend para resetar
                            fetch('/reseta-progresso', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                            })
                                .then(res => res.json())
                                .then(() => {
                                    btn.innerHTML = 'Enviar mensagens';
                                    btn.disabled = false;
                                    alert('Envio finalizado!');

                                    // Se quiser resetar visualmente também:
                                    document.getElementById('barraProgresso').style.width = '0%';
                                    document.getElementById('barraProgresso').innerText = '0%';
                                    document.getElementById('progressoTexto').innerText = '0/0';

                                    /*// Limpar checkboxes
                                    document.querySelectorAll('#formSelecaoColunas input[type="checkbox"]').forEach(checkbox => {
                                        checkbox.checked = false;
                                    });


                                    // Limpar textarea da mensagem, se quiser:
                                    document.getElementById('messageTextarea').value = '';

                                    //Limpar Preview
                                    document.getElementById('messagePreview').innerHTML = `
    <p class="text-muted text-center">A prévia da mensagem será exibida aqui.</p>
`;*/
                                    // Atualiza a página
                                    //location.reload();
                                });
                        }, 1000);
                    }
                });
        }, 1000);


        document.getElementById('btnFecharModal').addEventListener('click', () => {
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando mensagens...';
            btn.disabled = true;
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEnvio'));
            modal.hide();

            document.getElementById('msgBackground').style.display = 'block';
        });
    }

    // Funções principais

    // Detecta automaticamente quais colunas contêm nome e número de WhatsApp
    function detectImportantColumns(selectedColumns) {
        const mapping = {
            name_column: null,
            whatsapp_column: null
        };

        // Detectar coluna de nome
        const possibleNameColumns = ['nome', 'name', 'cliente', 'customer', 'contato', 'contact'];
        for (const column of selectedColumns) {
            if (possibleNameColumns.some(term => column.toLowerCase().includes(term))) {
                mapping.name_column = column;
                break;
            }
        }

        // Detectar coluna de WhatsApp/telefone
        const possiblePhoneColumns = ['whatsapp', 'telefone', 'phone', 'celular', 'mobile', 'número', 'number', 'contato', 'contact'];
        for (const column of selectedColumns) {
            if (possiblePhoneColumns.some(term => column.toLowerCase().includes(term))) {
                mapping.whatsapp_column = column;
                break;
            }
        }

        // Se não encontrou nome, usar a primeira coluna que não é telefone
        if (!mapping.name_column && selectedColumns.length > 0) {
            for (const column of selectedColumns) {
                if (column !== mapping.whatsapp_column) {
                    mapping.name_column = column;
                    break;
                }
            }
        }

        return mapping;
    }

    // Cria JSON estruturado para envio ao Laravel
    function createStructuredJson(selectedColumns, columnMapping) {
        const contacts = [];

        // Converter dados da planilha em uma estrutura JSON adequada
        previewData.forEach(row => {
            const contact = {};

            // Adicionar todos os campos selecionados
            selectedColumns.forEach(column => {
                const columnIndex = colunasDisponiveis.indexOf(column);
                if (columnIndex !== -1) {
                    // Tratar valores nulos ou vazios
                    const value = row[columnIndex] !== undefined && row[columnIndex] !== null
                        ? String(row[columnIndex]).trim()
                        : '';
                    contact[column] = value;
                }
            });

            // Processar número de WhatsApp - remover formatação
            if (columnMapping.whatsapp_column && contact[columnMapping.whatsapp_column]) {
                contact[columnMapping.whatsapp_column] = formatWhatsAppNumber(contact[columnMapping.whatsapp_column]);
            }

            contacts.push(contact);
        });

        return contacts;
    }

    // Formata número de WhatsApp para padrão internacional
    function formatWhatsAppNumber(number) {
        if (!number) return '';

        // Remover tudo que não for dígito
        let cleanNumber = String(number).replace(/\D/g, '');

        // Adicionar código do país (55) se não tiver e for número brasileiro
        if (cleanNumber.length === 11 || cleanNumber.length === 10) {
            cleanNumber = '55' + cleanNumber;
        } else if (cleanNumber.length === 9 || cleanNumber.length === 8) {
            // Números locais sem DDD - não podemos processar corretamente
            console.warn('Número sem DDD detectado:', cleanNumber);
        }

        return cleanNumber;
    }

    // Função de leitura do arquivo Excel
    function handleFileSelect(evt) {
        const file = evt.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheet = workbook.Sheets[workbook.SheetNames[0]];

            // Converter para JSON mantendo cabeçalhos
            const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });

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

            showTablePreview([colunasDisponiveis, ...previewData]);

            document.getElementById('fileName').innerText = file.name;
            document.getElementById('fileSize').innerText = (file.size / 1024).toFixed(2) + ' KB';
            document.getElementById('fileDetails').style.display = 'block';
            document.getElementById('tablePreview').style.display = 'block';

            document.getElementById('btnProximoPasso1').disabled = false;
        };
        reader.readAsArrayBuffer(file);
    }

    // Processa dados de Excel
    function processExcelData(workbook) {
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
        const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });

        if (jsonData.length < 2) {
            alert('O arquivo precisa ter pelo menos 1 linha de dados.');
            return;
        }

        // Obter cabeçalhos (primeira linha)
        colunasDisponiveis = jsonData[0].map(header => header ? String(header).trim() : `Coluna ${Math.random().toString(36).substr(2, 5)}`);

        // Filtrar linhas vazias
        previewData = jsonData.slice(1).filter(row => {
            return row.some(cell => cell !== null && cell !== undefined && String(cell).trim() !== '');
        });

        // Converter para formato de objeto usando os cabeçalhos
        jsonOriginalData = previewData.map(row => {
            const obj = {};
            colunasDisponiveis.forEach((header, index) => {
                obj[header] = row[index] !== undefined ? row[index] : '';
            });
            return obj;
        });

        updateFileInfo(file);
        showTablePreview([colunasDisponiveis, ...previewData]);
        enableNextButton();
    }

    // Processa dados de CSV
    function processCsvData(results) {
        if (!results.data || results.data.length === 0) {
            alert('O arquivo CSV não contém dados.');
            return;
        }

        // Obter cabeçalhos
        colunasDisponiveis = Object.keys(results.data[0]);

        // Dados para exibição
        const tableData = [colunasDisponiveis];
        results.data.forEach(row => {
            const rowData = [];
            colunasDisponiveis.forEach(header => {
                rowData.push(row[header] || '');
            });
            tableData.push(rowData);
        });

        // Filtrar dados
        previewData = tableData.slice(1).filter(row => {
            return row.some(cell => cell !== null && cell !== undefined && String(cell).trim() !== '');
        });

        // Manter dados originais em formato de objeto
        jsonOriginalData = results.data;

        updateFileInfo(file);
        showTablePreview(tableData);
        enableNextButton();
    }

    // Atualiza informações do arquivo
    function updateFileInfo(file) {
        if (!file) return;

        document.getElementById('fileName').innerText = file.name;
        document.getElementById('fileSize').innerText = (file.size / 1024).toFixed(2) + ' KB';
        document.getElementById('fileDetails').style.display = 'block';
        document.getElementById('tablePreview').style.display = 'block';
    }

    // Habilita botão de próximo
    function enableNextButton() {
        const btnNext = document.getElementById('btnProximoPasso1');
        if (btnNext) {
            btnNext.disabled = false;
        }
    }

    // Reseta input de arquivo
    function resetFileInput() {
        if (fileInput) {
            fileInput.value = '';
        }
        document.getElementById('fileDetails').style.display = 'none';
        document.getElementById('tablePreview').style.display = 'none';
        document.getElementById('btnProximoPasso1').disabled = true;
    }

    // Exibe prévia da tabela
    function showTablePreview(data) {
        if (!previewTable) return;

        previewTable.innerHTML = '';

        data.forEach((row, index) => {
            const tr = document.createElement('tr');

            // Destacar linhas vazias
            const isEmpty = row.every(cell => cell === null || cell === undefined || String(cell).trim() === '');
            if (isEmpty && index > 0) {
                tr.classList.add('table-secondary');
            }

            // Adicionar número da linha
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
                cellElem.textContent = cell !== undefined && cell !== null ? cell : '';
                tr.appendChild(cellElem);
            });

            previewTable.appendChild(tr);
        });
    }

    // Atualiza a barra de progresso
    function updateProgress(percent) {
        if (!progressBar) return;

        progressBar.style.width = percent + '%';
        progressBar.setAttribute('aria-valuenow', percent);
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

    // Cria CHECKBOX para cada coluna
    function gerarCamposSelecao() {
        const form = document.getElementById('formSelecaoColunas');
        if (!form) return;

        form.innerHTML = '';

        colunasDisponiveis.forEach(coluna => {
            const div = document.createElement('div');
            div.className = 'form-check col-md-4';

            div.innerHTML = `
                <input class="form-check-input destaque-checkbox" type="checkbox" value="${coluna}" id="coluna-${coluna}">
                <label class="form-check-label fw-bold" for="coluna-${coluna}">
                    ${coluna}
                </label>
            `;

            form.appendChild(div);
        });
    }

    // Gera botões de placeholders
    function gerarBotoesPlaceholders(colunasSelecionadas) {
        if (!botoesPlaceholders) return;

        botoesPlaceholders.innerHTML = '';

        colunasSelecionadas.forEach(coluna => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-primary btn-sm me-2 mb-2';
            btn.textContent = coluna;

            btn.addEventListener('click', function () {
                const placeholder = '{{' + coluna + '}}';
                insertAtCursor(messageTextarea, placeholder);

                // Disparar evento para atualizar prévia
                const inputEvent = new Event('input', { bubbles: true });
                messageTextarea.dispatchEvent(inputEvent);
            });

            botoesPlaceholders.appendChild(btn);
        });
    }

    // Gera prévia da mensagem
    function gerarPrevia(colunasSelecionadas) {
        if (!messageTextarea || !messagePreview) return;

        function atualizarPrevia() {
            const row = previewData[0]; // Primeiro registro
            if (!row) {
                console.warn('Nenhum dado disponível para a prévia');
                return;
            }

            let msg = messageTextarea.value;

            colunasSelecionadas.forEach(coluna => {
                const colunaIndex = colunasDisponiveis.indexOf(coluna);
                let valorReal = '';

                if (colunaIndex >= 0 && row[colunaIndex] !== undefined) {
                    valorReal = String(row[colunaIndex])
                        .replace(/[\r\n]+/g, '')
                        .trim();
                }

                const regex = new RegExp('\\{\\{' + coluna + '\\}\\}', 'g');
                msg = msg.replace(regex, valorReal);
            });

            // 👇 Aqui transformamos \n em <br> para visualização correta
            const msgFormatada = msg.replace(/\n/g, '<br>');

            messagePreview.innerHTML = `<p>${msgFormatada}</p>`;
        }

        messageTextarea.addEventListener('input', atualizarPrevia);

        atualizarPrevia(); // Executa na inicialização
    }

    // Insere texto no cursor do textarea
    function insertAtCursor(textarea, text) {
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const value = textarea.value;

        textarea.value = value.substring(0, start) + text + value.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + text.length;
        textarea.focus();
    }

    //document.getElementById('selectRegistroVisualizar').style.display = 'none';
});
