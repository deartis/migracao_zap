document.addEventListener('DOMContentLoaded', function () {
    const btnImportar = document.getElementById('btn-importar-contatos');
    const listaContatos = document.getElementById('lista-contatos');
    const contatosCheckboxes = document.getElementById('contatos-checkboxes');
    const selecionarTodos = document.getElementById('selecionar-todos');
    const pesquisarContatos = document.getElementById('pesquisar-contatos');
    const contadorSelecionados = document.getElementById('contador-selecionados');

    const confirmarSelecaoBtn = document.getElementById('confirmar-selecao');
    const contatosSelecionadosLista = document.getElementById('contatos-selecionados-lista');
    const contadorSelecionadosCard = document.getElementById('contador-selecionados-card');
    const limparSelecaoBtn = document.getElementById('limpar-selecao');

    // Seleciona os elementos
    const fileInput = document.getElementById('arquivo');
    const customButton = document.getElementById('custom-file-button');
    const fileChosen = document.getElementById('file-chosen');

    // Adiciona o evento de clique ao botão personalizado
    customButton.addEventListener('click', function () {
        fileInput.click();
    });

    // Exibe o nome do arquivo quando selecionado
    fileInput.addEventListener('change', function () {
        if (fileInput.value) {
            // Extrai apenas o nome do arquivo do caminho completo
            const fileName = fileInput.value.split('\\').pop();
            fileChosen.textContent = fileName;
        } else {
            fileChosen.textContent = 'Nenhum arquivo selecionado';
        }
    });

    // Função para atualizar o contador de selecionados
    function atualizarContador() {
        const totalSelecionados = document.querySelectorAll('input[name="contatos[]"]:checked').length;
        const contadorModal = document.getElementById('contador-selecionados-modal');
        if (contadorModal) {
            contadorModal.textContent = `${totalSelecionados} selecionado${totalSelecionados !== 1 ? 's' : ''}`;
        }
    }
    // Função para obter iniciais do nome
    function obterIniciais(nome) {
        if (!nome || nome.trim() === '' || nome === 'undefined') return '?';
        const partes = nome.trim().split(' ');
        if (partes.length === 1) return partes[0].charAt(0).toUpperCase();
        return (partes[0].charAt(0) + partes[partes.length - 1].charAt(0)).toUpperCase();
    }

    // Exibir loading durante a importação
    function mostrarLoading() {
        btnImportar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Importando...';
        btnImportar.disabled = true;
    }

    function ocultarLoading() {
        btnImportar.innerHTML = '<i class="bi bi-whatsapp me-1"></i> Importar contatos';
        btnImportar.disabled = false;
    }

    btnImportar.addEventListener('click', async () => {
        mostrarLoading();

        try {
            const response = await fetch(window.whatsappApiUrl + '/contacts', {
                headers: {
                    Authorization: 'Bearer ' + window.whatsappApiToken,
                }
            });

            const data = await response.json();
            const contatos = data.contacts;

            // Filtra apenas os contatos com @c.us
            const contatosValidos = contatos.filter(c => c.id.endsWith('@c.us'));

            contatosCheckboxes.innerHTML = ''; // Limpa antes de adicionar

            if (contatosValidos.length === 0) {
                contatosCheckboxes.innerHTML = `
                        <li class="list-group-item text-center py-4">
                            <i class="bi bi-exclamation-circle text-muted fs-3"></i>
                            <p class="mb-0 mt-2">Nenhum contato encontrado</p>
                        </li>
                    `;
            } else {
                contatosValidos.forEach((contato, index) => {
                    const nome = contato.name || contato.number.split('@')[0];
                    const numero = contato.number;
                    const iniciais = obterIniciais(nome);

                    const li = document.createElement('li');
                    li.className = 'list-group-item p-0';
                    li.innerHTML = `
                            <div class="contato-item">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" name="contatos[]"
                                        value="${numero}" id="contato-${index}" data-nome="${nome}">
                                </div>
                                <div class="contato-avatar">
                                    ${iniciais}
                                </div>
                                <div class="contato-info">
                                    <p class="contato-nome">${nome}</p>
                                    <p class="contato-numero">${numero.split('@')[0]}</p>
                                </div>
                            </div>
                        `;
                    contatosCheckboxes.appendChild(li);

                    // Adicionar evento para atualizar contador
                    li.querySelector('input[type="checkbox"]').addEventListener('change', atualizarContador);
                });
            }

            if (listaContatos) {
                listaContatos.style.display = 'block';
            }
            atualizarContador();
        } catch (error) {
            console.error('Erro ao importar contatos:', error);
            contatosCheckboxes.innerHTML = `
                    <li class="list-group-item text-center py-4">
                        <i class="bi bi-exclamation-triangle text-danger fs-3"></i>
                        <p class="mb-0 mt-2">Erro ao carregar contatos. Tente novamente.</p>
                    </li>
                `;
            listaContatos.style.display = 'block';
        } finally {
            ocultarLoading();
        }
    });

    // Selecionar todos
    selecionarTodos.addEventListener('change', () => {
        const checkboxes = document.querySelectorAll('input[name="contatos[]"]');
        checkboxes.forEach(cb => cb.checked = selecionarTodos.checked);
        atualizarContador();
    });

    // Pesquisar contatos
    pesquisarContatos.addEventListener('input', function (e) {
        const termo = e.target.value.toLowerCase();
        const itens = contatosCheckboxes.querySelectorAll('li');

        itens.forEach(item => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (!checkbox) return;

            const nome = checkbox.getAttribute('data-nome').toLowerCase();
            const numero = checkbox.value.toLowerCase();

            if (nome.includes(termo) || numero.includes(termo)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Exemplo de envio final (integração com backend depois)
    document.getElementById('btn-enviar-mensagem').addEventListener('click', () => {
        const mensagem = document.getElementById('mensagem').value;
        if (!mensagem.trim()) {
            alert('Por favor, digite uma mensagem antes de enviar.');
            return;
        }

        const selecionados = Array.from(document.querySelectorAll('input[name="contatos[]"]:checked')).map(cb => ({
            numero: cb.value,
            nome: cb.getAttribute('data-nome') || cb.value.split('@')[0]
        }));
        if (selecionados.length === 0) {
            alert('Selecione pelo menos um contato para enviar a mensagem.');
            return;
        }

        // Exibir confirmação
        if (confirm(`Enviar mensagem para ${selecionados.length} contato(s)?`)) {
            console.log('Mensagem:', mensagem);
            console.log('Enviar para:', selecionados);

            fetch('/envia-mensagem-em-massa-contstos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    mensagem: mensagem,
                    contatos: selecionados
                })
            })
                .then(response => response.json())
             .then(data => {
                if(data.message){
                    alert(data.message);
                }else if(data.error){
                    alert(data.error);
                }else{
                    alert('Mensagem enviada com sucesso!');
                }
             })
             .catch(error => {
                 console.error('Erro ao enviar mensagem:', error);
                 alert('Erro ao enviar mensagem. Tente novamente.');
             });

            // Aqui você envia para um controller Laravel com fetch ou axios
            // Exemplo:
            // fetch('/api/enviar-mensagem', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            //     },
            //     body: JSON.stringify({
            //         mensagem: mensagem,
            //         contatos: selecionados
            //     })
            // })
            // .then(response => response.json())
            // .then(data => {
            //     alert('Mensagem enviada com sucesso!');
            // })
            // .catch(error => {
            //     console.error('Erro ao enviar mensagem:', error);
            //     alert('Erro ao enviar mensagem. Tente novamente.');
            // });
        }
    });

    document.getElementById('confirmar-selecao').addEventListener('click', () => {
        const selecionados = Array.from(document.querySelectorAll('input[name="contatos[]"]:checked'));

        const listaSelecionados = document.getElementById('contatos-selecionados-lista');
        listaSelecionados.innerHTML = ''; // Limpa antes de adicionar

        if (selecionados.length === 0) {
            listaSelecionados.innerHTML = `
            <li class="list-group-item text-center text-muted">
                Nenhum contato selecionado
            </li>
        `;
        } else {
            selecionados.forEach(cb => {
                const nome = cb.getAttribute('data-nome') || cb.value.split('@')[0];
                const numero = cb.value.split('@')[0];

                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.innerHTML = `
                <div>
                    <strong>${nome}</strong><br>
                    <small class="text-muted">${numero}</small>
                </div>
                <button class="btn btn-sm btn-outline-danger btn-remover" data-numero="${cb.value}">
                    <i class="bi bi-x-circle"></i>
                </button>
            `;
                listaSelecionados.appendChild(li);
            });
        }

        // Atualizar contador no card da esquerda
        document.getElementById('contador-selecionados-card').textContent = `${selecionados.length} selecionado${selecionados.length !== 1 ? 's' : ''}`;

        // Reativar botão de remover
        ativarRemocaoIndividual();
    });

    function ativarRemocaoIndividual() {
        document.querySelectorAll('.btn-remover').forEach(botao => {
            botao.addEventListener('click', function () {
                const numero = this.getAttribute('data-numero');

                // Desmarca no modal
                const checkbox = document.querySelector(`input[name="contatos[]"][value="${numero}"]`);
                if (checkbox) checkbox.checked = false;

                // Remove da lista de selecionados
                this.closest('li').remove();

                // Atualiza contador no modal
                atualizarContador();

                // Atualiza contador no card
                const novosSelecionados = document.querySelectorAll('#contatos-selecionados-lista li').length;
                document.getElementById('contador-selecionados-card').textContent = `${novosSelecionados} selecionado${novosSelecionados !== 1 ? 's' : ''}`;

                // Se ficou vazio, mostrar texto padrão
                if (novosSelecionados === 0) {
                    document.getElementById('contatos-selecionados-lista').innerHTML = `
                    <li class="list-group-item text-center text-muted">
                        Nenhum contato selecionado
                    </li>
                `;
                }
            });
        });
    }
    limparSelecaoBtn.addEventListener('click', () => {
        // Desmarca todos os checkboxes
        const checkboxes = document.querySelectorAll('input[name="contatos[]"]');
        checkboxes.forEach(cb => cb.checked = false);

        // Atualiza o contador no modal
        atualizarContador();

        // Limpa a lista de contatos selecionados
        contatosSelecionadosLista.innerHTML = `
        <li class="list-group-item text-center text-muted">
            Nenhum contato selecionado
        </li>
    `;
        contadorSelecionadosCard.textContent = '0 selecionados';
    });


    document.getElementById('limpar-selecao').addEventListener('click', () => {
        // Desmarca todos no modal
        document.querySelectorAll('input[name="contatos[]"]').forEach(cb => cb.checked = false);

        // Limpa a lista visual
        document.getElementById('contatos-selecionados-lista').innerHTML = `
        <li class="list-group-item text-center text-muted">
            Nenhum contato selecionado
        </li>
    `;

        // Zera os contadores
        atualizarContador();
        document.getElementById('contador-selecionados-card').textContent = '0 selecionados';
    });

    confirmarSelecaoBtn.addEventListener('click', () => {
        // Limpa a lista atual
        contatosSelecionadosLista.innerHTML = '';

        // Obtém os checkboxes selecionados
        const checkboxesSelecionados = document.querySelectorAll('input[name="contatos[]"]:checked');

        if (checkboxesSelecionados.length === 0) {
            contatosSelecionadosLista.innerHTML = `
            <li class="list-group-item text-center text-muted">
                Nenhum contato selecionado
            </li>
        `;
            contadorSelecionadosCard.textContent = '0 selecionados';
            return;
        }

        // Adiciona os contatos selecionados à lista
        checkboxesSelecionados.forEach(cb => {
            const nome = cb.getAttribute('data-nome');
            const numero = cb.value.split('@')[0];

            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.textContent = `${nome} - ${numero}`;
            contatosSelecionadosLista.appendChild(li);
        });

        // Atualiza o contador
        contadorSelecionadosCard.textContent = `${checkboxesSelecionados.length} selecionado${checkboxesSelecionados.length > 1 ? 's' : ''}`;
    });


});
