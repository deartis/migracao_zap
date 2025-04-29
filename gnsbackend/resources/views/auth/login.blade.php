@extends('layout.auth')

@section('content')
    <div class="container">
        <div class="login-container">
            <h2>Login</h2>
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
           {{-- @auth
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Você já está logado como {{ auth()->user()->name }}.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @else
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Você não está logado.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
            @endauth--}}

            <form action="{{ route('login') }}" method="post">
                @csrf
                <!-- Campo de E-mail -->
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Digite seu e-mail" required>
                    @error('email')
                        <div class="invalid-feedback">
                            {{$message}}
                        </div>
                    @enderror
                </div>

                <!-- Campo de Senha -->
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Digite sua senha" required>
                    @error('password')
                    <div class="invalid-feedback">
                        {{$message}}}
                    </div>
                    @enderror
                </div>

                <!-- Botão de Login -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </div>

                <!-- Links adicionais -->
                <div class="mt-3 text-center">
                    <a href="#" class="text-decoration-none">Esqueceu sua senha?</a>
                </div>
            </form>
        </div>
    </div>
@endsection
