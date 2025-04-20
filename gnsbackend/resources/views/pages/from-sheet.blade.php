@extends('layout.app') {{-- Ou o nome do seu layout base --}}

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg rounded-4">
                    <div class="card-header bg-primary text-white text-center fs-5">
                        Upload de Arquivo CSV
                    </div>
                    <div class="card-body">
                        <form action="{{ route('csv.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">Selecione o arquivo CSV</label>
                                <input class="form-control" type="file" name="csv_file" id="csv_file" accept=".csv"
                                       required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-upload"></i> Enviar CSV
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
