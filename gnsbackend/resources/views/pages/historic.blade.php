@extends('layout.app')

@section('content')
    <div class="container mt-5">
        <h2 class="mb-4 text-center fw-bold">📊 Registro de Envio de Mensagens</h2>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-lg rounded-4 bg-light">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary"><i class="bi bi-people-fill"></i> Contatos</h5>
                        <p class="fs-3 fw-bold">{{ $totalContatos }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-lg rounded-4 bg-light">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success"><i class="bi bi-check2-circle"></i> Enviadas</h5>
                        <p class="fs-3 fw-bold">{{ $enviadas }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-lg rounded-4 bg-light">
                    <div class="card-body text-center">
                        <h5 class="card-title text-danger"><i class="bi bi-x-circle-fill"></i> Erros</h5>
                        <p class="fs-3 fw-bold">{{ $comErro }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-lg rounded-4">
            <div class="card-header bg-dark text-white rounded-top-3">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Últimos Envios</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-bordered mb-0 rounded-bottom-4">
                    <thead class="table-secondary">
                    <tr>
                        <th>Nome</th>
                        <th>Número</th>
                        <th>Status</th>
                        <th>Erro</th>
                        <th>Data/Hora</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($historico as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->contact }}</td>
                            <td>
                                @if($item->status === 'enviado')
                                    <span class="badge bg-success">Enviado</span>
                                @else
                                    <span class="badge bg-danger">Erro</span>
                                @endif
                            </td>
                            <td>{{ $item->errorType ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y - H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Nenhum envio ainda.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
