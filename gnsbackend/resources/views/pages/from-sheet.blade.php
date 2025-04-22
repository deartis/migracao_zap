@extends('layout.app') {{-- Ou o nome do seu layout base --}}

@section('content')
    <div class="container py-1">
        @if (session('success'))
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
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-lg rounded-4">
                    <div class="card-header bg-primary text-white text-center fs-5">
                        Upload de Arquivo (.CSV .XLSX .XSL .XML .ODS)
                    </div>
                    <div class="card-body">
                        <form action="{{ route('upload.sheet') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">Selecione o arquivo</label>
                                <input class="form-control" type="file" name="csv_file" id="csv_file"
                                       accept=".csv, .xls, .xlsx, .xml, .ods"
                                       required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-upload"></i> Enviar Lista
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="mt-5">Contatos Importados</h4>

        @if($contatos->isEmpty())
            <div class="alert alert-info">Nenhum contato foi importado ainda.</div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mt-3">
                    <thead class="table-success">
                    <tr>
                        <th>Nome</th>
                        <th>Número</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($contatos as $contato)
                        <tr>
                            <td>{{ $contato->name }}</td>
                            <td>{{ $contato->contact }}</td>
                            <td>{{ ucfirst($contato->status) }}</td>
                            <td>{{ \Carbon\Carbon::parse($contato->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
@endsection
