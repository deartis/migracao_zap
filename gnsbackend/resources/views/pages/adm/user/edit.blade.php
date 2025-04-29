@extends('layout.app')

@section('content')
    <div class="container mt-4">
        <h1>Editar Usuário</h1>

        <!-- Mensagens de erro/sucesso -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <!-- Nome -->
                <div class="col-md-6">
                    <label for="name" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="{{ old('name', $user->name) }}" required>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="{{ old('email', $user->email) }}" required>
                </div>

                <!-- Número -->
                <div class="col-md-6">
                    <label for="number" class="form-label">Número</label>
                    <input type="text" class="form-control" id="number" name="number"
                           value="{{ old('number', $user->number) }}">
                </div>

                <!-- Limite de Mensagens -->
                <div class="col-md-6">
                    <label for="msgLimit" class="form-label">Limite de Mensagens</label>
                    <input type="number" class="form-control" id="msgLimit" name="msgLimit"
                           value="{{ old('msgLimit', $user->msgLimit) }}">
                </div>

                <!-- Role (Tipo de Usuário) -->
                <div class="col-md-6">
                    <label for="role" class="form-label">Tipo de Usuário</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="nu" {{ $user->role == 'nu' ? 'selected' : '' }}>Usuário Normal</option>
                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Administrador</option>
                    </select>
                </div>

                <!-- Status (Ativo/Inativo) -->
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <div class="form-check">
                        <input type="hidden" name="enabled" value="0">
                        <input class="form-check-input" type="checkbox" id="enabled" name="enabled"
                            value="1" {{ $user->enabled ? 'checked' : '' }}>
                        <label class="form-check-label" for="enabled">Ativo</label>
                    </div>
                </div>

                <!-- Botões -->
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar Alterações
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
@endsection
