@extends('layout.app')

@section('content')
    <div class="container">
        <div class="login-container">
            <h2>Registro</h2>
            <form action="{{ route('register') }}" method="post">
                @csrf

                <!-- Campo de Nome -->
                <div class="mb-3">
                    <label for="name" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Digite seu nome" required>
                </div>

                <!-- Campo de E-mail -->
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu e-mail" required>
                </div>

                <!-- Campo de Senha -->
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Digite sua senha" required>
                </div>

                <!-- Campo de Telefone -->
                <div class="mb-3">
                    <label for="phone" class="form-label">Telefone</label>
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Digite seu telefone" required>
                </div>

                <!-- Campo de Limite de Mensagens -->
                <div class="mb-3">
                    <label for="message_limit" class="form-label">Limite de Mensagens</label>
                    <input type="number" class="form-control" id="message_limit" name="message_limit" placeholder="Digite o limite de mensagens" min="0" required>
                </div>

                <!-- Campo de Tipo de Usuário -->
                <div class="mb-3">
                    <label for="user_type" class="form-label">Tipo de Usuário</label>
                    <select class="form-select" id="user_type" name="user_type" required>
                        <option value="" disabled selected>Selecione o tipo de usuário</option>
                        <option value="normal">Normal</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <!-- Botão de Registro -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>

                <!-- Link para Login -->
                <div class="mt-3 text-center">
                    <a href="{{ route('login') }}" class="text-decoration-none">Já tem uma conta? Faça login</a>
                </div>
            </form>
        </div>
    </div>
@endsection
