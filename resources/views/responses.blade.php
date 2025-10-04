@extends('layouts.app')

@section('title', 'Respuestas de Mensajes')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Mensajes Recibidos</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($mensajes->isEmpty())
        <p>No hay mensajes todavía.</p>
    @else
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Teléfono</th>
                    <th>Mensaje</th>
                    <th>Fecha</th>
                    <th>Responder</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mensajes as $index => $mensaje)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $mensaje->from }}</td>
                    <td>{{ $mensaje->message }}</td>
                    <td>{{ $mensaje->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <form action="{{ route('messages.reply') }}" method="POST" class="d-flex">
                            @csrf
                            <input type="hidden" name="phone" value="{{ $mensaje->from }}">
                            <input type="text" name="message" class="form-control me-2" placeholder="Escribe tu respuesta" required>
                            <button type="submit" class="btn btn-success">Enviar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
