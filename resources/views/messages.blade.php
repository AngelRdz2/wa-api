@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Mensajes Recibidos</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Teléfono</th>
                <th>Mensaje</th>
                <th>Fecha</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mensajes as $msg)
                <tr>
                    <td>{{ $msg->phone }}</td>
                    <td>{{ $msg->message }}</td>
                    <td>{{ $msg->created_at }}</td>
                    <td>
                        <form action="{{ route('messages.reply') }}" method="POST">
                            @csrf
                            <input type="hidden" name="phone" value="{{ $msg->phone }}">
                            <input type="text" name="message" placeholder="Escribe tu respuesta">
                            <button class="btn btn-primary btn-sm">Responder</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
