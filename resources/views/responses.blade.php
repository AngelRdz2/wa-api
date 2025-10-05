@extends('layouts.app')

@section('title', 'Respuestas de Mensajes')

@push('styles')
<style>
.chat-container {
    max-width: 800px;
    margin: auto;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    font-family: sans-serif;
}

.chat-header {
    font-size: 1.5rem;
    margin-bottom: 20px;
    text-align: center;
}

.chat-thread {
    margin-bottom: 40px;
}

.chat-bubble {
    max-width: 75%;
    padding: 10px 15px;
    border-radius: 15px;
    margin-bottom: 10px;
    position: relative;
    word-wrap: break-word;
}

.inbound {
    background-color: #e5e5ea;
    color: #000;
    align-self: flex-start;
    border-top-left-radius: 0;
}

.outbound {
    background-color: #dcf8c6;
    color: #000;
    align-self: flex-end;
    border-top-right-radius: 0;
    margin-left: auto;
}

.chat-meta {
    font-size: 0.75em;
    color: #555;
    margin-top: 5px;
    text-align: right;
}

.chat-input {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.chat-input input {
    flex: 1;
    padding: 10px;
    border-radius: 20px;
    border: 1px solid #ccc;
}

.chat-input button {
    padding: 10px 20px;
    border-radius: 20px;
    background-color: #25d366;
    color: white;
    border: none;
    cursor: pointer;
}
</style>
@endpush

@section('content')
<div class="chat-container">
    <div class="chat-header">Mensajes Recibidos</div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($mensajes->isEmpty())
        <p>No hay mensajes todavía.</p>
    @else
        @foreach($mensajes->groupBy('from_number') as $numero => $conversacion)
            <div class="chat-thread">
                <h5><strong>{{ $numero }}</strong></h5>

                @foreach($conversacion as $mensaje)
                    <div class="chat-bubble {{ $mensaje->direction === 'inbound' ? 'inbound' : 'outbound' }}">
                        <div>{{ $mensaje->message }}</div>
                        <div class="chat-meta">{{ $mensaje->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                @endforeach

                <form action="{{ route('responses.reply') }}" method="POST" class="chat-input">
                    @csrf
                    <input type="hidden" name="phone" value="{{ $numero }}">
                    <input type="text" name="message" placeholder="Escribe tu respuesta..." required>
                    <button type="submit">Enviar</button>
                </form>
            </div>
        @endforeach
    @endif
</div>
@endsection
