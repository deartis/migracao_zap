@extends('layout.app')

@section('content')
    <div class="container" style="max-width: 600px; margin: 0 auto; padding-top: 30px;">
        <h2>Responder Mensagem</h2>

        @if(session('success'))
            <div style="color: green; margin-bottom: 10px;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('whatsapp.responder') }}" method="POST">
            @csrf

            <div style="margin-bottom: 15px;">
                <label for="numero" style="display:block; margin-bottom:5px;">Número do WhatsApp:</label>
                <input type="text" id="numero" name="numero" required
                       placeholder="Ex: 5598999999999"
                       style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="mensagem" style="display:block; margin-bottom:5px;">Mensagem:</label>
                <textarea id="mensagem" name="mensagem" required
                          placeholder="Digite sua resposta aqui..."
                          rows="5"
                          style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;"></textarea>
            </div>

            <button type="submit" style="padding: 10px 20px; background-color: #28a745; border: none; color: white; border-radius: 5px; cursor: pointer;">
                Enviar Resposta
            </button>
        </form>
    </div>
@endsection
