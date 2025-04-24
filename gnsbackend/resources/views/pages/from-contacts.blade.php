@extends('layout.app')

@section('content')
    <div class="container mt-5">
        <div class="card shadow rounded-4">
            <div class="card-header bg-primary text-white rounded-top-4">
                <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Enviar Mensagem em Massa</h4>
            </div>
            <div class="card-body p-4">
                @if(session('success'))
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

                <form action="{{ route('whatsapp.send.bulk') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label for="message" class="form-label fw-bold">Mensagem</label>
                        <textarea name="message" id="message" rows="5" class="form-control @error('message') is-invalid @enderror" placeholder="Digite sua mensagem aqui...">{{ old('message') }}</textarea>
                        @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="file" class="form-label fw-bold">Arquivo</label>
                        <div class="input-group">
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                            <label class="input-group-text" for="file"><i class="fas fa-upload"></i></label>
                        </div>
                        <div class="form-text">Formatos suportados: PDF, JPEG, PNG (máx. 2MB)</div>
                        @error('file')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    {{--<div class="mb-4">
                        <label for="contacts_file" class="form-label fw-bold">Importar Contatos (opcional)</label>
                        <div class="input-group">
                            <input type="file" class="form-control @error('contacts_file') is-invalid @enderror" id="contacts_file" name="contacts_file" accept=".csv,.xlsx">
                            <label class="input-group-text" for="contacts_file"><i class="fas fa-address-book"></i></label>
                        </div>
                        <div class="form-text">Faça upload de uma lista de contatos em formato CSV ou Excel</div>
                        @error('contacts_file')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>--}}

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success py-2 fw-bold">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Mensagem
                        </button>

                        {{--<button type="reset" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-2"></i>Limpar Formulário
                        </button>--}}
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Adicionar visualização prévia do arquivo selecionado
            $('#file').change(function() {
                const fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $(this).next('.input-group-text').html('<i class="fas fa-check me-1"></i> ' + fileName);
                } else {
                    $(this).next('.input-group-text').html('<i class="fas fa-upload"></i>');
                }
            });

            // Adicionar visualização prévia do arquivo de contatos
            $('#contacts_file').change(function() {
                const fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $(this).next('.input-group-text').html('<i class="fas fa-check me-1"></i> ' + fileName);
                } else {
                    $(this).next('.input-group-text').html('<i class="fas fa-address-book"></i>');
                }
            });
        });
    </script>
@endsection
