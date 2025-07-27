@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6 bg-white rounded-lg shadow-md mt-6">
    <h2 class="text-3xl font-bold text-blue-600 mb-6 text-center">Vista previa de mensajes</h2>

    @foreach ($mensajesPorCategoria as $categoria => $mensajes)
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-indigo-700 mb-3">Categoría: {{ $categoria }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300">
                    <thead class="bg-indigo-600 text-white">
                        <tr>
                            <th class="px-4 py-2 border border-indigo-700">Teléfono</th>
                            <th class="px-4 py-2 border border-indigo-700">Mensaje a Enviar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mensajes as $item)
                            <tr class="hover:bg-indigo-100">
                                <td class="border border-indigo-700 px-4 py-2">{{ $item['numero'] }}</td>
                                <td class="border border-indigo-700 px-4 py-2">{{ $item['mensaje'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <form action="{{ route('messages.send') }}" method="POST" class="text-center">
        @csrf
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded shadow mt-4 transition duration-300">
            Confirmar y Enviar Mensajes
        </button>
    </form>
</div>
@endsection
