@extends('layout.app')
@section('content')
    <div class="container py-4">
        <div class="">
            <h2 class="text-center mb-4">Envio de mensagem em massa - Chats Ativos</h2>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row">
                    <!-- Card de chats selecionados (à esquerda) -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Chats selecionados</h5>
                            </div>
                            <div class="card-body p-0">
                                <div id="chats-selecionados-wrapper" style="max-height: 300px; overflow-y: auto;">
                                    <ul class="list-group list-group-flush" id="chats-selecionados-lista">
                                        <!-- Chats selecionados serão inseridos aqui via JavaScript -->
                                        <li class="list-group-item text-center text-muted">
                                            Nenhum chat selecionado
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
                                    <div class="form-text">Esta mensagem será enviada para todos os chats selecionados.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <!-- Botão para carregar chats ativos -->
                        <div class="">
                            <button class="btn btn-primary" id="btn-importar-chats" data-bs-toggle="modal"
                                    data-bs-target="#modalChats">
                                <i class="bi bi-chat-dots me-1"></i>Carregar chats
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

    <!-- Modal para seleção de chats -->
    <div class="modal fade" id="modalChats" tabindex="-1" aria-labelledby="modalChatsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalChatsLabel">Selecionar Chats Ativos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Barra de pesquisa -->
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="pesquisar-chats"
                               placeholder="Pesquisar chats...">
                    </div>

                    <!-- Cabeçalho da lista de chats -->
                    <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="selecionar-todos">
                            <label class="form-check-label fw-bold" for="selecionar-todos">
                                Selecionar todos os chats
                            </label>
                        </div>
                        <span class="badge bg-primary" id="contador-selecionados-modal">0 selecionados</span>
                    </div>

                    <!-- Lista de chats -->
                    <div id="chats-wrapper" style="max-height: 50vh; overflow-y: auto;">
                        <ul class="list-group list-group-flush" id="chats-checkboxes">
                            <!-- Chats serão inseridos aqui via JavaScript -->
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
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let contatos = [];
            let selecionados = [];

            const checkboxContainer = document.getElementById('chats-checkboxes');
            const selecionarTodosCheckbox = document.getElementById('selecionar-todos');
            const contadorModal = document.getElementById('contador-selecionados-modal');
            const contadorCard = document.getElementById('contador-selecionados-card');
            const listaSelecionados = document.getElementById('chats-selecionados-lista');
            const limparSelecaoBtn = document.getElementById('limpar-selecao');
            const pesquisarInput = document.getElementById('pesquisar-chats');

            // Carrega contatos da rota
            document.getElementById('btn-importar-chats').addEventListener('click', () => {
                fetch("{{ route('get.contatos') }}")
                    .then(res => res.json())
                    .then(data => {
                        contatos = data;
                        renderizarCheckboxes(data);
                    });
            });

            // Renderiza os checkboxes
            function renderizarCheckboxes(lista) {
                checkboxContainer.innerHTML = '';
                lista.forEach(contato => {
                    const item = document.createElement('li');
                    item.className = 'list-group-item';

                    const checkboxId = 'check-' + contato.telefone;

                    item.innerHTML = `
                <div class="form-check">
                    <input class="form-check-input chat-checkbox" type="checkbox" value="${contato.telefone}" id="${checkboxId}" data-nome="${contato.nome}">
                    <label class="form-check-label" for="${checkboxId}">
                        ${contato.nome} - ${contato.telefone}
                    </label>
                </div>
            `;

                    checkboxContainer.appendChild(item);
                });
            }

            // Selecionar todos
            selecionarTodosCheckbox.addEventListener('change', (e) => {
                document.querySelectorAll('.chat-checkbox').forEach(cb => {
                    cb.checked = e.target.checked;
                });
                atualizarSelecionadosTemp();
            });

            // Atualiza lista temporária ao clicar em checkboxes
            checkboxContainer.addEventListener('change', () => atualizarSelecionadosTemp());

            // Busca por nome ou telefone
            pesquisarInput.addEventListener('input', () => {
                const termo = pesquisarInput.value.toLowerCase();
                const filtrados = contatos.filter(c =>
                    c.nome.toLowerCase().includes(termo) || c.telefone.includes(termo)
                );
                renderizarCheckboxes(filtrados);
            });

            // Confirmar seleção
            document.getElementById('confirmar-selecao').addEventListener('click', () => {
                selecionados = [];
                document.querySelectorAll('.chat-checkbox:checked').forEach(cb => {
                    selecionados.push({
                        telefone: cb.value,
                        nome: cb.dataset.nome
                    });
                });
                atualizarCardSelecionados();
            });

            // Limpar seleção
            limparSelecaoBtn.addEventListener('click', () => {
                selecionados = [];
                atualizarCardSelecionados();
            });

            // Atualiza os selecionados no card
            function atualizarCardSelecionados() {
                listaSelecionados.innerHTML = '';

                if (selecionados.length === 0) {
                    listaSelecionados.innerHTML = `<li class="list-group-item text-center text-muted">Nenhum chat selecionado</li>`;
                } else {
                    selecionados.forEach(c => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item';
                        li.setAttribute('data-telefone', c.telefone);
                        li.textContent = `${c.nome} - ${c.telefone}`;
                        listaSelecionados.appendChild(li);
                    });
                }

                contadorCard.textContent = `${selecionados.length} selecionado(s)`;
            }

            // Atualiza contador no modal
            function atualizarSelecionadosTemp() {
                const total = document.querySelectorAll('.chat-checkbox:checked').length;
                contadorModal.textContent = `${total} selecionado(s)`;
            }
        });
    </script>
    {{-- Botão para carregar arquivos --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputArquivo = document.getElementById('arquivo');
            const customButton = document.getElementById('custom-file-button');
            const fileChosen = document.getElementById('file-chosen');

            // Quando clica no botão estilizado, abre o input de arquivo oculto
            customButton.addEventListener('click', () => {
                inputArquivo.click();
            });

            // Quando seleciona um arquivo, atualiza o nome no texto ao lado
            inputArquivo.addEventListener('change', () => {
                if (inputArquivo.files.length > 0) {
                    fileChosen.textContent = inputArquivo.files[0].name;
                } else {
                    fileChosen.textContent = 'Nenhum arquivo selecionado';
                }
            });
        });
    </script>

    {{-- Envia para o controller  --}}
    <script>
        document.getElementById('btn-enviar-mensagem').addEventListener('click', function () {
            const mensagem = document.getElementById('mensagem').value;
            const arquivo = document.getElementById('arquivo').files[0];

            const numerosSelecionados = [];
            document.querySelectorAll('#chats-selecionados-lista li[data-telefone]').forEach(li => {
                numerosSelecionados.push(li.getAttribute('data-telefone'));
            });

            if (numerosSelecionados.length === 0) {
                alert('Selecione pelo menos um contato.');
                return;
            }

            if (!mensagem.trim()) {
                alert('Digite a mensagem.');
                return;
            }

            const formData = new FormData();
            formData.append('mensagem', mensagem);
            formData.append('contatos', JSON.stringify(numerosSelecionados));
            if (arquivo) {
                formData.append('arquivo', arquivo);
            }

            axios.post("{{ route('enviar.mensagem.massa.contatos') }}", formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then(response => {
                    alert('Mensagens enviadas com sucesso!');
                    console.log(response.data);
                })
                .catch(error => {
                    console.error(error);
                    alert('Erro ao enviar mensagens.');
                });
        });
    </script>
@endpush





