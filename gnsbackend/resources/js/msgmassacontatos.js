document.addEventListener('DOMContentLoaded', function () {
    const btnImportar = document.getElementById('btn-importar-chats');
    const listaChats = document.getElementById('lista-chats');
    const chatsCheckboxes = document.getElementById('chats-checkboxes');
    const selecionarTodos = document.getElementById('selecionar-todos');
    const pesquisarChats = document.getElementById('pesquisar-chats');
    const contadorSelecionados = document.getElementById('contador-selecionados');

    const confirmarSelecaoBtn = document.getElementById('confirmar-selecao');
    const chatsSelecionadosLista = document.getElementById('chats-selecionados-lista');
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
            fileChosen.textContent = fileInput.value.split('\\').pop();
        } else {
            fileChosen.textContent = 'Nenhum arquivo selecionado';
        }
    });

    // Função para atualizar o contador de selecionados
    function atualizarContador() {
        const totalSelecionados = document.querySelectorAll('input[name="chats[]"]:checked').length;
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

    // Função para formatar o nome do contato
    function formatarNomeContato(chat) {
        // Prioriza: name > pushname > número formatado
        if (chat.contact.name) return chat.contact.name;
        if (chat.contact.pushname) return chat.contact.pushname;
        return chat.id; // Retorna o número como fallback
    }

    // Exibir loading durante a importação
    function mostrarLoading() {
        btnImportar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Carregando...';
        btnImportar.disabled = true;
    }

    function ocultarLoading() {
        btnImportar.innerHTML = '<i class="bi bi-chat-dots me-1"></i> Carregar chats';
        btnImportar.disabled = false;
    }

    btnImportar.addEventListener('click', async () => {
        mostrarLoading();

        try {
            console.log(window.whatsgwApiKey);
            // Substitua pela sua URL e configurações do WhatsGW
            const response = await fetch(window.whatsgwApiUrl + '/GetAllChats', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    apikey: window.whatsgwApiKey,
                    phone_number: window.whatsgwPhoneNumber
                })
            });

            const data = await response.json();

            // Filtra apenas chats individuais (não grupos)
            const chatsIndividuais = data.chats.filter(chat =>
                chat.contact && !chat.contact.isGroup && chat.id !== window.whatsgwPhoneNumber
            );

            chatsCheckboxes.innerHTML = ''; // Limpa antes de adicionar

            if (chatsIndividuais.length === 0) {
                chatsCheckboxes.innerHTML = `
                    <li class="list-group-item text-center py-4">
                        <i class="bi bi-exclamation-circle text-muted fs-3"></i>
                        <p class="mb-0 mt-2">Nenhum chat ativo encontrado</p>
                    </li>
                `;
            } else {
                chatsIndividuais.forEach((chat, index) => {
                    const nome = formatarNomeContato(chat);
                    const numero = chat.id;
                    const iniciais = obterIniciais(nome);

                    const li = document.createElement('li');
                    li.className = 'list-group-item p-0';
                    li.innerHTML = `
                        <div class="contato-item">
                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" name="chats[]"
                                    value="${numero}" id="chat-${index}" data-nome="${nome}">
                            </div>
                            <div class="contato-avatar">
                                ${iniciais}
                            </div>
                            <div class="contato-info">
                                <p class="contato-nome">${nome}</p>
                                <p class="contato-numero">${numero}</p>
                            </div>
                        </div>
                    `;
                    chatsCheckboxes.appendChild(li);

                    // Adicionar evento para atualizar contador
                    li.querySelector('input[type="checkbox"]').addEventListener('change', atualizarContador);
                });
            }

            if (listaChats) {
                listaChats.style.display = 'block';
            }
            atualizarContador();
        } catch (error) {
            console.error('Erro ao carregar chats:', error);
            chatsCheckboxes.innerHTML = `
                <li class="list-group-item text-center py-4">
                    <i class="bi bi-exclamation-triangle text-danger fs-3"></i>
                    <p class="mb-0 mt-2">Erro ao carregar chats. Tente novamente.</p>
                </li>
            `;
            listaChats.style.display = 'block';
        } finally {
            ocultarLoading();
        }
    });

    // Selecionar todos
    selecionarTodos.addEventListener('change', () => {
        const checkboxes = document.querySelectorAll('input[name="chats[]"]');
        checkboxes.forEach(cb => cb.checked = selecionarTodos.checked);
        atualizarContador();
    });

    // Pesquisar chats
    pesquisarChats.addEventListener('input', function (e) {
        const termo = e.target.value.toLowerCase();
        const itens = chatsCheckboxes.querySelectorAll('li');

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

    // Envio de mensagem
    document.getElementById('btn-enviar-mensagem').addEventListener('click', () => {
        const mensagem = document.getElementById('mensagem').value;
        if (!mensagem.trim()) {
            alert('Por favor, digite uma mensagem antes de enviar.');
            return;
        }

        const selecionados = Array.from(document.querySelectorAll('input[name="chats[]"]:checked')).map(cb => ({
            numero: cb.value,
            nome: cb.getAttribute('data-nome') || cb.value
        }));

        if (selecionados.length === 0) {
            alert('Selecione pelo menos um chat para enviar a mensagem.');
            return;
        }

        // Exibir confirmação
        if (confirm(`Enviar mensagem para ${selecionados.length} chat(s)?`)) {
            console.log('Mensagem:', mensagem);
            console.log('Enviar para:', selecionados);

            fetch('/envia-mensagem-em-massa-chats', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    mensagem: mensagem,
                    chats: selecionados
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                    } else if (data.error) {
                        alert(data.error);
                    } else {
                        alert('Mensagem enviada com sucesso!');
                    }
                })
                .catch(error => {
                    console.error('Erro ao enviar mensagem:', error);
                    alert('Erro ao enviar mensagem. Tente novamente.');
                });
        }
    });

    // Confirmar seleção
    document.getElementById('confirmar-selecao').addEventListener('click', () => {
        const selecionados = Array.from(document.querySelectorAll('input[name="chats[]"]:checked'));

        const listaSelecionados = document.getElementById('chats-selecionados-lista');
        listaSelecionados.innerHTML = ''; // Limpa antes de adicionar

        if (selecionados.length === 0) {
            listaSelecionados.innerHTML = `
            <li class="list-group-item text-center text-muted">
                Nenhum chat selecionado
            </li>
        `;
        } else {
            selecionados.forEach(cb => {
                const nome = cb.getAttribute('data-nome') || cb.value;
                const numero = cb.value;

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
                const checkbox = document.querySelector(`input[name="chats[]"][value="${numero}"]`);
                if (checkbox) checkbox.checked = false;

                // Remove da lista de selecionados
                this.closest('li').remove();

                // Atualiza contador no modal
                atualizarContador();

                // Atualiza contador no card
                const novosSelecionados = document.querySelectorAll('#chats-selecionados-lista li').length;
                document.getElementById('contador-selecionados-card').textContent = `${novosSelecionados} selecionado${novosSelecionados !== 1 ? 's' : ''}`;

                // Se ficou vazio, mostrar texto padrão
                if (novosSelecionados === 0) {
                    document.getElementById('chats-selecionados-lista').innerHTML = `
                    <li class="list-group-item text-center text-muted">
                        Nenhum chat selecionado
                    </li>
                `;
                }
            });
        });
    }

    limparSelecaoBtn.addEventListener('click', () => {
        // Desmarca todos os checkboxes
        const checkboxes = document.querySelectorAll('input[name="chats[]"]');
        checkboxes.forEach(cb => cb.checked = false);

        // Atualiza o contador no modal
        atualizarContador();

        // Limpa a lista de chats selecionados
        chatsSelecionadosLista.innerHTML = `
        <li class="list-group-item text-center text-muted">
            Nenhum chat selecionado
        </li>
    `;
        contadorSelecionadosCard.textContent = '0 selecionados';
    });

    document.getElementById('limpar-selecao').addEventListener('click', () => {
        // Desmarca todos no modal
        document.querySelectorAll('input[name="chats[]"]').forEach(cb => cb.checked = false);

        // Limpa a lista visual
        document.getElementById('chats-selecionados-lista').innerHTML = `
        <li class="list-group-item text-center text-muted">
            Nenhum chat selecionado
        </li>
    `;

        // Zera os contadores
        atualizarContador();
        document.getElementById('contador-selecionados-card').textContent = '0 selecionados';
    });

    confirmarSelecaoBtn.addEventListener('click', () => {
        // Limpa a lista atual
        chatsSelecionadosLista.innerHTML = '';

        // Obtém os checkboxes selecionados
        const checkboxesSelecionados = document.querySelectorAll('input[name="chats[]"]:checked');

        if (checkboxesSelecionados.length === 0) {
            chatsSelecionadosLista.innerHTML = `
            <li class="list-group-item text-center text-muted">
                Nenhum chat selecionado
            </li>
        `;
            contadorSelecionadosCard.textContent = '0 selecionados';
            return;
        }

        // Adiciona os chats selecionados à lista
        checkboxesSelecionados.forEach(cb => {
            const nome = cb.getAttribute('data-nome');
            const numero = cb.value;

            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.textContent = `${nome} - ${numero}`;
            chatsSelecionadosLista.appendChild(li);
        });

        // Atualiza o contador
        contadorSelecionadosCard.textContent = `${checkboxesSelecionados.length} selecionado${checkboxesSelecionados.length > 1 ? 's' : ''}`;
    });
});
