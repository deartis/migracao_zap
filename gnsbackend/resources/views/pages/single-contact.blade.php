@extends('layout.app')
@section('content')
    <div class="container py-4">
        <div class="">
            <h2 class="text-center mb-4">Envio de mensagem individual</h2>
        </div>
        <div class="card shadow-sm">
            <div id="resultado"></div>
            <form id="form-msg_unica" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <!-- Card de contatos selecionados (à esquerda) -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Contatos</h5>
                                </div>

                                <!-- Campo de busca -->
                                <div class="card-body p-3 pb-0">
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text"
                                                   id="busca-contato"
                                                   class="form-control"
                                                   placeholder="Buscar...">
                                        </div>
                                    </div>
                                </div>

                                <!-- Contato selecionado -->
                                <div id="contato-selecionado" class="mx-3 mb-3" style="display: none;">
                                    <div class="alert alert-success mb-0 p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong id="nome-selecionado"></strong><br>
                                                <small id="telefone-selecionado"></small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="limparSelecao()">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lista de contatos -->
                                <div class="card-body p-0 pt-0">
                                    <div id="contatos-wrapper" style="max-height: 350px; overflow-y: auto;">
                                        <ul class="list-group list-group-flush" id="lista-contatos">
                                            @foreach ($contatos as $contato)
                                                @foreach ($contato->contacts as $item)
                                                    <li class="list-group-item list-group-item-action contato-item"
                                                        data-nome="{{ strtolower($item['nome']) }}"
                                                        data-telefone="{{ $item['telefone'] }}"
                                                        onclick="selecionarContato('{{ $item['nome'] }}', '{{ $item['telefone'] }}')">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong
                                                                    class="nome-contato">{{ $item['nome'] }}</strong><br>
                                                                <small
                                                                    class="text-muted telefone-contato">{{ $item['telefone'] }}</small>
                                                            </div>
                                                            <i class="bi bi-chevron-right text-muted"></i>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            @endforeach
                                        </ul>
                                        <div id="sem-resultados" class="text-center p-4 text-muted"
                                             style="display: none;">
                                            <i class="bi bi-search me-2"></i>
                                            Nenhum contato encontrado
                                        </div>
                                    </div>
                                </div>

                                <!-- Inputs ocultos -->
                                <input type="hidden" id="contato" name="contato">
                                <input type="hidden" id="nomeContato" name="nome">
                            </div>
                        </div>

                        <!-- Card da mensagem (à direita) -->
                        <div class="col-md-8 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="bi bi-chat-text-fill me-2"></i>Mensagem</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="row">
                                            <div class="col-12">
                                                <!-- Botão para importar arquivo -->
                                                <div class="mb-3">
                                                    <!-- Input file original (oculto) -->
                                                    <input type="file" class="d-none" name="arquivo" id="arquivo">

                                                    <!-- Botão personalizado com ícone -->
                                                    <button type="button" class="btn btn-primary "
                                                            id="custom-file-button">
                                                        <i class="bi bi-paperclip me-2"></i>Adicionar arquivo
                                                    </button>

                                                    <!-- Elemento para mostrar o arquivo selecionado -->
                                                    <div id="arquivo-selecionado" class="mt-2" style="display: none;">
                                                        <div class="alert alert-info p-2 mb-0">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <i class="bi bi-file-earmark me-2"></i>
                                                                    <span id="nome-arquivo"></span>
                                                                </div>
                                                                <button type="button"
                                                                        class="btn btn-sm btn-outline-danger"
                                                                        onclick="removerArquivo()">
                                                                    <i class="bi bi-x"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <textarea id="mensagem_unica"
                                                  class="form-control"
                                                  rows="8"
                                                  placeholder="Digite sua mensagem aqui..."></textarea>
                                        <div class="form-text mt-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Selecione um contato para enviar a mensagem.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-success btn-lg" id="btn-enviar" disabled>
                                <i class="bi bi-send-fill me-2"></i> Enviar mensagem
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @push('styles')
        <style>
            .contato-item:hover {
                background-color: #f8f9fa;
                cursor: pointer;
                transform: translateX(2px);
                transition: all 0.2s ease;
            }

            .contato-item.active {
                background-color: #d4edda;
                border-left: 4px solid #28a745;
            }

            #custom-file-button:hover {
                transform: translateY(-1px);
                transition: all 0.2s ease;
            }

            .alert {
                border-radius: 8px;
            }

            .card {
                border-radius: 12px;
                border: none;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            .card-header {
                border-radius: 5px 5px 0 0 !important;
            }

            .btn {
                border-radius: 5px;
            }

            .form-control {
                border-radius: 8px;
            }

            .input-group-text {
                border-radius: 8px 0 0 8px;
            }

            .spinner-border-sm {
                width: 1rem;
                height: 1rem;
            }
        </style>
    @endpush
    @push('scripts')
        <script>
            // Função para selecionar contato
            function selecionarContato(nome, telefone) {
                document.getElementById('contato').value = telefone;
                document.getElementById('nomeContato').value = nome;

                // Mostrar contato selecionado
                document.getElementById('nome-selecionado').textContent = nome;
                document.getElementById('telefone-selecionado').textContent = telefone;
                document.getElementById('contato-selecionado').style.display = 'block';

                // Habilitar botão de envio
                document.getElementById('btn-enviar').disabled = false;

                // Focar no campo de mensagem
                document.getElementById('mensagem_unica').focus();

                // Limpar busca
                document.getElementById('busca-contato').value = '';
                filtrarContatos();
            }

            // Função para limpar seleção
            function limparSelecao() {
                document.getElementById('contato').value = '';
                document.getElementById('nomeContato').value = '';
                document.getElementById('contato-selecionado').style.display = 'none';
                document.getElementById('btn-enviar').disabled = true;
            }

            // Função para filtrar contatos
            function filtrarContatos() {
                const busca = document.getElementById('busca-contato').value.toLowerCase();
                const contatos = document.querySelectorAll('.contato-item');
                let encontrados = 0;

                contatos.forEach(contato => {
                    const nome = contato.getAttribute('data-nome');
                    const telefone = contato.getAttribute('data-telefone');

                    if (nome.includes(busca) || telefone.includes(busca)) {
                        contato.style.display = 'block';
                        encontrados++;
                    } else {
                        contato.style.display = 'none';
                    }
                });

                // Mostrar/ocultar mensagem de "sem resultados"
                document.getElementById('sem-resultados').style.display =
                    encontrados === 0 && busca !== '' ? 'block' : 'none';
            }

            // Função para remover arquivo
            function removerArquivo() {
                document.getElementById('arquivo').value = '';
                document.getElementById('arquivo-selecionado').style.display = 'none';
            }

            // Event listeners
            document.addEventListener('DOMContentLoaded', function () {
                // Busca de contatos
                document.getElementById('busca-contato').addEventListener('input', filtrarContatos);

                // Seleção de arquivo personalizada
                document.getElementById('custom-file-button').addEventListener('click', function () {
                    document.getElementById('arquivo').click();
                });

                // Mostrar arquivo selecionado
                document.getElementById('arquivo').addEventListener('change', function () {
                    const arquivo = this.files[0];
                    if (arquivo) {
                        document.getElementById('nome-arquivo').textContent = arquivo.name;
                        document.getElementById('arquivo-selecionado').style.display = 'block';
                    }
                });

                // Submissão do formulário
                document.getElementById('form-msg_unica').addEventListener('submit', function (e) {
                    e.preventDefault();

                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const contato = document.getElementById('contato').value;
                    const mensagem = document.getElementById('mensagem_unica').value;
                    const arquivoInput = document.getElementById('arquivo');
                    const nome = document.getElementById('nomeContato').value;
                    const arquivo = arquivoInput.files[0];

                    // Validações
                    if (!contato) {
                        alert('Por favor, selecione um contato.');
                        return;
                    }

                    if (!mensagem && !arquivo) {
                        alert('Por favor, digite uma mensagem ou selecione um arquivo.');
                        return;
                    }

                    // Desabilitar botão durante o envio
                    const btnEnviar = document.getElementById('btn-enviar');
                    btnEnviar.disabled = true;
                    btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

                    if (arquivo) {
                        const reader = new FileReader();
                        reader.onload = function () {
                            const base64 = reader.result.split(',')[1];
                            const dados = {
                                contato: contato,
                                nome: nome,
                                mensagem: mensagem,
                                arquivo: {
                                    nome: arquivo.name,
                                    mimetype: arquivo.type,
                                    base64: base64
                                }
                            };

                            enviarMensagem(dados, token, btnEnviar);
                        };
                        reader.readAsDataURL(arquivo);
                    } else {
                        const dados = {
                            contato: contato,
                            nome: nome,
                            mensagem: mensagem
                        };

                        enviarMensagem(dados, token, btnEnviar);
                    }
                });
            });

            // Função para enviar mensagem
            function enviarMensagem(dados, token, btnEnviar) {
                axios.post('/page/single-contact/send', dados, {
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                })
                    .then(response => {
                        console.log(response.data);
                        document.getElementById('resultado').innerHTML =
                            '<div class="alert alert-success mt-3"><i class="bi bi-check-circle me-2"></i>Mensagem enviada com sucesso!</div>';

                        // Limpar formulário
                        document.getElementById('mensagem_unica').value = '';
                        removerArquivo();

                        // Remover alerta após 5 segundos
                        setTimeout(() => {
                            document.getElementById('resultado').innerHTML = '';
                        }, 5000);
                    })
                    .catch(error => {
                        console.log(error.response.data);
                        document.getElementById('resultado').innerHTML =
                            '<div class="alert alert-danger mt-3"><i class="bi bi-exclamation-triangle me-2"></i>Erro ao enviar a mensagem. Tente novamente.</div>';
                    })
                    .finally(() => {
                        // Reabilitar botão
                        btnEnviar.disabled = false;
                        btnEnviar.innerHTML = '<i class="bi bi-send-fill me-2"></i> Enviar mensagem';
                    });
            }
        </script>
    @endpush

@endsection
{{--@extends('layout.app')
@section('content')
    <div class="container py-4">
        <div class="">
            <h2 class="text-center mb-4">Envio de mensagem individual</h2>
        </div>
        <div class="card shadow-sm">
            --}}{{-- <div class="card-header bg-light">
                <h4 class="mb-0">Enviar mensagem em massa</h4>
            </div> --}}{{--
            <div id="resultado"></div>
            <form id="form-msg_unica" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <!-- Card de contatos selecionados (à esquerda) -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    --}}{{--<h5 class="mb-0">Contatos</h5>--}}{{--
                                    <div class=" text-center">
                                        <span id="exibeNome">--}}{{-- O nome do contato e exibido aqui --}}{{--</span>
                                    </div>
                                    <input type="text" placeholder="Digite ou cliquem em um contato" id="contato" name="contato" class="form-group text-center">
                                    <input type="hidden" id="nomeContato" name="nome" class="form-group text-center">

                                </div>
                                <div class="card-body p-0">
                                    <div id="contatos-selecionados-wrapper"
                                         style="max-height: 300px; overflow-y: auto;">
                                        <ul class="list-group">
                                            @foreach ($contatos as $contato)
                                                @foreach ($contato->contacts as $item)
                                                    <li class="list-group-item list-group-item-action"
                                                        onclick="selecionarContato({{ json_encode($item['nome']) }}, {{ json_encode($item['telefone']) }})">
                                                        <strong>{{ $item['nome'] }}</strong><br>
                                                        <small>{{ $item['telefone'] }}</small>
                                                    </li>
                                                @endforeach
                                            @endforeach
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
                                        --}}{{--<div class="form-text">Esta mensagem será enviada para todos os contatos selecionados.
                                        </div>--}}{{--
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Botão para importar contatos da API -->
                            <div class="">
                                --}}{{--<button class="btn btn-primary" id="btn-importar-contatos" data-bs-toggle="modal"
                                        data-bs-target="#modalContatos">
                                    <i class="bi bi-whatsapp me-1"></i>Carregar contatos
                                </button>--}}{{--
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-success" --}}{{--id="btn-enviar-mensagem"--}}{{-->
                                <i class="bi bi-send-fill me-1"></i> Enviar mensagem
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @push('scripts')
        <script>
            function selecionarContato(nome, telefone) {
                document.getElementById('contato').value = telefone;
                document.getElementById('nomeContato').value = nome;
                document.getElementById('exibeNome').text = nome;
                document.getElementById('mensagem').focus();
            }
        </script>

        <script>
            document.getElementById('form-msg_unica').addEventListener('submit', function (e) {
                e.preventDefault();

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const contato = document.getElementById('contato').value;
                const mensagem = document.getElementById('mensagem_unica').value;
                const arquivoInput = document.getElementById('arquivo');
                const nome = document.getElementById('nomeContato').value;
                const arquivo = arquivoInput.files[0];

                if (arquivo) {
                    const reader = new FileReader();
                    reader.onload = function () {
                        const base64 = reader.result.split(',')[1]; // Remove o prefixo data:...;base64,
                        const dados = {
                            contato: contato,
                            nome: nome,
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
                        nome: nome,
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
        </script>
    @endpush

@endsection--}}
{{--/*document.getElementById('form-msg_unica').addEventListener('submit', function (e) {
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
});*/--}}
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
