{{-- resources/views/messages-preview.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Vista Previa de Mensajes</h1>

    @if(session('status'))
        <div class="bg-green-100 text-green-800 p-2 mb-4 rounded">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-2 mb-4 rounded">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if(empty($mensajesPorCategoria))
        <p class="text-gray-600">No hay mensajes para mostrar.</p>
        <a href="{{ route('excel.upload') }}" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Volver a Subir Excel
        </a>
    @else
        <form action="{{ route('messages.send') }}" method="POST">
            @csrf

            @foreach($mensajesPorCategoria as $categoria => $mensajes)
                <div class="mb-6 border rounded p-4 bg-gray-50">
                    <h2 class="text-xl font-semibold mb-2">{{ $categoria }}</h2>
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="border px-2 py-1 text-left">Número</th>
                                <th class="border px-2 py-1 text-left">Mensaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mensajes as $msg)
                                <tr>
                                    <td class="border px-2 py-1">{{ $msg['numero'] }}</td>
                                    <td class="border px-2 py-1">{{ $msg['mensaje'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

            <div class="flex gap-4 mt-4">
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Confirmar y Enviar Mensajes
                </button>
                <a href="{{ route('excel.upload') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Volver a Subir Excel
                </a>
            </div>
        </form>
    @endif
</div>
@endsection
