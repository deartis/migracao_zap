@extends('layout.app')

@section('content')
    <div class="container py-4">
        <h1 class="mb-4">Contatos do WhatsApp</h1>

        <div class="row mb-4">
            <div class="col">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Lista de Contatos</h5>
                        <div>
                            <button id="refreshContacts" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Atualizar
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="search-box">
                            <input type="text" id="searchContact" class="form-control" placeholder="Buscar contatos...">
                        </div>

                        <div class="selection-actions mb-3">
                            <button id="selectAll" class="btn btn-sm btn-outline-primary">Selecionar Todos</button>
                            <button id="deselectAll" class="btn btn-sm btn-outline-secondary">Desmarcar Todos</button>
                            <span id="selectedCount" class="ms-3">0 contatos selecionados</span>
                        </div>

                        <div id="loadingContacts" class="loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </div>

                        <div id="contactsError" class="alert alert-danger d-none">
                            Erro ao carregar contatos. Tente novamente.
                        </div>

                        <div id="contactsList" class="contact-list d-none">
                            <!-- Os contatos serão carregados aqui via JavaScript -->
                        </div>
                    </div>
                    <div class="card-footer">
                        <button id="processSelected" class="btn btn-primary" disabled>
                            Processar Selecionados
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection''
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Elementos do DOM
            const contactsList = document.getElementById('contactsList');
            const loadingContacts = document.getElementById('loadingContacts');
            const contactsError = document.getElementById('contactsError');
            const searchContact = document.getElementById('searchContact');
            const selectAll = document.getElementById('selectAll');
            const deselectAll = document.getElementById('deselectAll');
            const selectedCount = document.getElementById('selectedCount');
            const processSelected = document.getElementById('processSelected');
            const refreshContacts = document.getElementById('refreshContacts');

            // Variáveis globais
            let contacts = [];
            let selectedContacts = [];

            // Função para carregar contatos
            function loadContacts() {
                contactsList.classList.add('d-none');
                loadingContacts.classList.remove('d-none');
                contactsError.classList.add('d-none');

                fetch('{{--{{ route("whatsapp.api.contacts") }}--}}')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erro ao carregar contatos');
                        }
                        return response.json();
                    })
                    .then(data => {
                        contacts = data.contacts || [];
                        renderContacts(contacts);
                        loadingContacts.classList.add('d-none');
                        contactsList.classList.remove('d-none');
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        loadingContacts.classList.add('d-none');
                        contactsError.classList.remove('d-none');
                    });
            }

            // Função para renderizar os contatos
            function renderContacts(contactsToRender) {
                contactsList.innerHTML = '';

                if (contactsToRender.length === 0) {
                    contactsList.innerHTML = '<div class="text-center py-4">Nenhum contato encontrado</div>';
                    return;
                }

                contactsToRender.forEach(contact => {
                    const contactElement = document.createElement('div');
                    contactElement.className = 'contact-item';

                    // Iniciais para o avatar se não houver foto
                    const initials = getInitials(contact.name || 'Sem Nome');

                    contactElement.innerHTML = `
                        <div class="form-check me-2">
                            <input class="form-check-input contact-checkbox" type="checkbox"
                                   value="${contact.id}" id="contact-${contact.id}">
                        </div>
                        <div class="contact-avatar">${initials}</div>
                        <div class="contact-info">
                            <div class="contact-name">${contact.name || 'Sem Nome'}</div>
                            <div class="contact-number">${contact.number || ''}</div>
                        </div>
                    `;

                    contactsList.appendChild(contactElement);

                    // Adicionar evento para o checkbox
                    const checkbox = contactElement.querySelector('.contact-checkbox');
                    checkbox.addEventListener('change', function () {
                        if (this.checked) {
                            selectedContacts.push(contact.id);
                        } else {
                            selectedContacts = selectedContacts.filter(id => id !== contact.id);
                        }
                        updateSelectedCount();
                    });
                });
            }

            // Função para obter iniciais do nome
            function getInitials(name) {
                return name
                    .split(' ')
                    .map(part => part.charAt(0))
                    .join('')
                    .toUpperCase()
                    .substring(0, 2);
            }

            // Função para atualizar contador de selecionados
            function updateSelectedCount() {
                const count = selectedContacts.length;
                selectedCount.textContent = `${count} contatos selecionados`;
                processSelected.disabled = count === 0;
            }

            // Função para filtrar contatos
            function filterContacts(query) {
                if (!query) {
                    renderContacts(contacts);
                    return;
                }

                const filtered = contacts.filter(contact => {
                    const name = (contact.name || '').toLowerCase();
                    const number = (contact.number || '').toLowerCase();
                    return name.includes(query.toLowerCase()) || number.includes(query.toLowerCase());
                });

                renderContacts(filtered);
            }

            // Função para processar contatos selecionados
            function processContacts() {
                if (selectedContacts.length === 0) return;

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('{{--{{ route("whatsapp.process.contacts") }}--}}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        contacts: selectedContacts
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Contatos processados com sucesso!');
                            // Aqui você pode redirecionar ou limpar a seleção
                            selectedContacts = [];
                            updateSelectedCount();

                            // Desmarcar todos os checkboxes
                            document.querySelectorAll('.contact-checkbox').forEach(checkbox => {
                                checkbox.checked = false;
                            });
                        } else {
                            alert('Erro ao processar contatos: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao processar contatos. Tente novamente.');
                    });
            }

            // Event listeners
            searchContact.addEventListener('input', function () {
                filterContacts(this.value);
            });

            selectAll.addEventListener('click', function () {
                document.querySelectorAll('.contact-checkbox').forEach(checkbox => {
                    checkbox.checked = true;

                    // Adicionar ID à lista de selecionados se não estiver lá
                    const contactId = checkbox.value;
                    if (!selectedContacts.includes(contactId)) {
                        selectedContacts.push(contactId);
                    }
                });
                updateSelectedCount();
            });

            deselectAll.addEventListener('click', function () {
                document.querySelectorAll('.contact-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                selectedContacts = [];
                updateSelectedCount();
            });

            processSelected.addEventListener('click', processContacts);

            refreshContacts.addEventListener('click', loadContacts);

            // Carregar contatos ao iniciar
            loadContacts();
        });
    </script>
@endsection

{{--
@extends('layout.app')

@section('content')
    @bloqueado
    <div class="alert alert-danger text-center">
        Sua conta está bloqueada. Por favor, entre em contato com o suporte.
    </div>
    @else
        <div class="container mt-5">
            <div class="card shadow rounded-4">
                <div class="card-header bg-primary text-white rounded-top-4">
                    <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Enviar Mensagem em Massa</h4>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('whatsapp.send.bulk') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="message" class="form-label fw-bold">Mensagem</label>
                            <textarea name="message" id="message" rows="5" class="form-control @error('message') is-invalid @enderror" placeholder="Digite sua mensagem aqui...">{{ old('message') }}</textarea>
                            @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 d-none">
                            <label for="file" class="form-label fw-bold">Arquivo</label>
                            <div class="input-group">
                                <input type="file" class="form-control @error('media') is-invalid @enderror" id="media" name="media">
                                <label class="input-group-text" for="file"><i class="fas fa-upload"></i></label>
                            </div>
                            <div class="form-text">Formatos suportados: PDF, JPEG, PNG (máx. 2MB)</div>
                            @error('file')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success py-2 fw-bold">
                                <i class="fas fa-paper-plane me-2"></i>Enviar Mensagem
                            </button>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endbloqueado

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Adicionar visualização prévia do arquivo selecionado
            $('#file').change(function() {
                const fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $(this).next('.input-group-text').html('<i class="fas fa-check me-1"></i> ' + fileName);
                } else {
                    $(this).next('.input-group-text').html('<i class="fas fa-upload"></i>');
                }
            });

            // Adicionar visualização prévia do arquivo de contatos
            $('#contacts_file').change(function() {
                const fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $(this).next('.input-group-text').html('<i class="fas fa-check me-1"></i> ' + fileName);
                } else {
                    $(this).next('.input-group-text').html('<i class="fas fa-address-book"></i>');
                }
            });
        });
    </script>
@endsection
--}}
