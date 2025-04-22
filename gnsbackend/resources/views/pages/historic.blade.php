@extends('layout.app')

@section('content')
    <div class="container mt-5">
        <div class="card shadow rounded-4">
            <div class="card-header bg-primary text-white rounded-top-4">
                <h4 class="mb-0">Enviar Mensagem em Massa</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('whatsapp.send.bulk') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="message" class="form-label">Mensagem</label>
                        <textarea name="message" id="message" rows="4" class="form-control" placeholder="Digite sua mensagem..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Enviar para todos os contatos</button>
                </form>
            </div>
        </div>
    </div>
@endsection

