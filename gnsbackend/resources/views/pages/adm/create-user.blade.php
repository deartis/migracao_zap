@extends('layout.app')

@section('content')
    <div class="container">
        <div class="login-container">
            <h2>Registro</h2>
            <form action="{{ route('add.user') }}" method="post">
                @csrf
                <!-- Campo de Nome -->
                <div class="mb-3">
                    <label for="name" class="form-label">Nome</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        name="name" placeholder="Digite seu nome" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Campo de E-mail -->
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                        name="email" placeholder="Digite seu e-mail" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Campo de Senha -->
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                        name="password" placeholder="Digite sua senha" value="{{ old('password') }}" required>
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Campo de confirmação de senha -->
                <div class="mb-3">
                    <label for="password" class="form-label">Confirme a Senha</label>
                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                        id="password_confirmation" name="password_confirmation" placeholder="Repita a senha" value="{{ old('password_confirmation') }}" required>
                    @error('password_confirmation')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Campo de Telefone -->
                <div class="mb-3">
                    <label for="number" class="form-label">Telefone</label>
                    <input type="tel" class="form-control @error('number') is-invalid @enderror" id="number"
                        name="number" placeholder="Digite seu telefone" value="{{ old('number') }}" required>
                    @error('number')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Campo de Limite de Mensagens -->
                <div class="mb-3">
                    <label for="msgLimit" class="form-label">Limite de Mensagens</label>
                    <input type="number" class="form-control @error('msgLimit') is-invalid @enderror"
                        id="msgLimit" name="msgLimit" placeholder="Digite o limite de mensagens" min="0" value="{{ old('msgLimit') }}"
                        required>
                    @error('message_limit')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Campo de Tipo de Usuário -->
                <div class="mb-3">
                    <label for="user_type" class="form-label">Tipo de Usuário</label>
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" value="{{ old('role') }}" required>
                        <option value="" disabled selected>Selecione o tipo de usuário</option>
                        <option value="nu">Normal</option>
                        <option value="admin">Admin</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Botão de Registro -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
