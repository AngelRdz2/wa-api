<div class="chat-box">
    @forelse($mensajes as $message)
        <div class="chat-bubble {{ $message->direction === 'inbound' ? 'inbound' : 'outbound' }}">
            <div class="chat-message">{{ $message->message }}</div>
            <div class="chat-meta">
                {{ $message->from }} · {{ $message->received_at->format('d/m/Y H:i') }}
            </div>
        </div>
    @empty
        <p>No hay mensajes.</p>
    @endforelse

    <form action="{{ route('messages.reply') }}" method="POST" class="chat-input">
        @csrf
        <input type="hidden" name="phone" value="{{ $mensajes->last()->from ?? '' }}">
        <input type="text" name="message" placeholder="Escribe tu respuesta..." required>
        <button type="submit">Enviar</button>
    </form>
</div>
