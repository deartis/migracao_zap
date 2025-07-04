@extends('layout.app')

@section('content')
    <div class="container py-4">
        <div class="row">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            @endif
            <div class="col-lg-4">
                <!-- Card do perfil -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @if ($user->avatar)
                                <img src="{{ $user->avatar }}" alt="Foto de perfil" class="rounded-circle img-fluid"
                                    style="width: 120px;">
                            @else
                                <div class="bg-light rounded-circle d-flex justify-content-center align-items-center mx-auto"
                                    style="width: 120px; height: 120px;">
                                    <i class="bi bi-person text-secondary" style="font-size: 50pt"></i>
                                </div>
                            @endif
                        </div>
                        <h5 class="mb-1">{{ $user->name }}</h5>
                        {{-- <p class="text-muted mb-3">{{ $user->role }}</p> --}}

                        {{-- <div class="d-flex justify-content-center mb-2">

                        </div> --}}
                    </div>
                </div>

                <!-- Card com estatísticas -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Estatísticas de Mensagens</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h6 class="text-muted mb-1">Limite de Mensagens</h6>
                                <h4>{{ $user->msgLimit }}</h4>
                            </div>
                            <div class="col-6 mb-3">
                                <h6 class="text-muted mb-1">Mensagens Enviadas</h6>
                                <h4>{{ $user->sendedMsg - $counttotalErros }}</h4>
                            </div>
                            <div class="col-12">
                                <div class="progress" style="height: 10px;">
                                    @php
                                        $percentage =
                                            $user->msgLimit > 0
                                                ? min(100, (($user->sendedMsg - $counttotalErros) / $user->msgLimit) * 100)
                                                : 0;
                                    @endphp
                                    <div class="progress-bar bg-primary" role="progressbar"
                                        style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">{{ $user->sendedMsg }} de {{ $user->msgLimit }} mensagens
                                    ({{ number_format($percentage, 1) }}%)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- Card com informações do usuário -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informações do Perfil</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Nome completo</h6>
                            </div>
                            <div class="col-sm-9">
                                <p class="text-muted mb-0">{{ $user->name }}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Email</h6>
                            </div>
                            <div class="col-sm-9">
                                <p class="text-muted mb-0">{{ $user->email }}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Número de telefone</h6>
                            </div>
                            <div class="col-sm-9">
                                <p class="text-muted mb-0">{{ $user->number ?? 'Não definido' }}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Status</h6>
                            </div>
                            <div class="col-sm-9">
                                @if ($user->enabled)
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-danger">Inativo</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card de ações -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Ações</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="fa-solid fa-key me-1"></i> Alterar Senha
                            </button>

                            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal"
                                data-bs-target="#editProfileModal">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Editar Perfil
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar perfil -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Editar Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('page.update.profile', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ $user->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ $user->email }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="number" class="form-label">Número de telefone</label>
                            <input type="tel" class="form-control" id="number" name="number"
                                value="{{ $user->number }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para alterar senha -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Alterar Senha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Senha atual</label>
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Nova senha</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar nova senha</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Alterar senha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para verificar número -->
    <div class="modal fade" id="verifyNumberModal" tabindex="-1" aria-labelledby="verifyNumberModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verifyNumberModalLabel">Verificar Número</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('page.profile') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="verification_code" class="form-label">Código de verificação</label>
                            <input type="text" class="form-control" id="verification_code" name="verification_code"
                                required>
                            <div class="form-text">Insira o código de verificação que foi enviado para o seu telefone.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Verificar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    @if ($errors->any())
        <script>
            // Verifica se algum erro está relacionado ao formulário de senha
            const hasPasswordError = {!! json_encode($errors->has('current_password') || $errors->has('password')) !!};

            if (hasPasswordError) {
                var passwordModal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
                passwordModal.show();
            }
        </script>
    @endif
@endpush
