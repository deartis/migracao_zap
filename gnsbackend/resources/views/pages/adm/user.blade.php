@extends('layout.app')

@section('content')
    <div class="container py-5">
        <!-- Search Bar and New User Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="input-group w-50 shadow-sm">
                <span class="input-group-text bg-white border-0">
                    <i class="bi bi-search text-primary"></i>
                </span>
                <input type="text" class="form-control border-0 py-2" placeholder="Pesquisar usuário" aria-label="Pesquisar usuário">
            </div>
            <a href="{{ route('adm.register.user') }}" class="btn btn-primary btn-lg px-4 shadow rounded-pill d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle fs-5"></i>
                <span>Novo Usuário</span>
            </a>
        </div>

        @session('success')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endsession

        <!-- Users Table -->
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="bg-body-secondary text-muted">
                        <tr>
                            <th scope="col" class="ps-4 py-3">#</th>
                            <th scope="col" class="py-3">Nome</th>
                            <th scope="col" class="py-3">Email</th>
                            <th scope="col" class="py-3">Mensagens</th>
                            <th scope="col" class="pe-4 py-3 text-end">Ações</th>
                        </tr>
                        </thead>
                        <tbody class="table-group-divider">
                        @php
                            $usuarios = [
                                [1, 'Clínica Perisse (22999110706)', 'elomedicinaintegrad@gmail.com', '0 / 4000'],
                                [2, 'Óculare (22999880206)', 'admocularecampos@hotmail.com', '0 / 1000'],
                                [3, 'César Hilário (2198092891)', 'cedormj@gmail.com', '0 / 1000'],
                                [4, 'Clauciel Eli Admin (22998243838)', 'clauciel@globalnetsis.com.br', '0 / 5000'],
                                [5, 'Clauciel Normal (22999664899)', 'clauciel@gmail.com', '0 / 300'],
                                [6, 'Clau, irmª (22998614947)', 'clauci@gmail.com', '0 / 300'],
                                [7, 'teste', 'teste@teste.com', '0 / 100'],
                                [8, 'jean.teste', 'jeansilva7035@gmail.com', '0 / 100'],
                            ];
                        @endphp

                        @foreach ($usuarios as [$id, $nome, $email, $mensagens])
                            <tr>
                                <td class="ps-4 fw-semibold text-secondary">{{ $id }}</td>
                                <td class="text-dark">{{ $nome }}</td>
                                <td class="text-muted">{{ $email }}</td>
                                <td class="text-dark">{{ $mensagens }}</td>
                                <td class="pe-4 text-end">
                                    <a href="#" class="btn btn-sm btn-outline-primary rounded-pill me-2" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-success rounded-pill me-2" title="Ativar">
                                        <i class="bi bi-check-circle"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger rounded-pill" title="Deletar Sessão">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
