@extends('layouts.app')

@section('content')
<div class="container mt-5 max-w-4xl mx-auto p-6 bg-white rounded shadow">

    <h2 class="text-2xl font-bold mb-6">Enviar mensajes masivos por categoría</h2>

    @if(session('status'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $categorias = session('numeros_por_categoria', []);
    @endphp

    @if(empty($categorias))
        <div class="p-3 bg-yellow-100 text-yellow-700 rounded">
            No hay números cargados. <a href="{{ route('excel.upload') }}" class="underline text-blue-600 hover:text-blue-800">Sube un archivo Excel primero.</a>
        </div>
    @else
        <form method="POST" action="{{ route('messages.send') }}">
            @csrf

            @foreach($categorias as $categoria => $numeros)
                <div class="mb-6">
                    <label for="message_{{ $categoria }}" class="block font-semibold mb-1">
                        Mensaje para <span class="text-indigo-600">{{ $categoria }}</span> ({{ count($numeros) }} números)
                    </label>
                    <textarea 
                        id="message_{{ $categoria }}" 
                        name="messages[{{ $categoria }}]" 
                        rows="4" 
                        required 
                        class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    ></textarea>
                </div>
            @endforeach

            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition">
                Enviar mensajes
            </button>
        </form>
    @endif

</div>
@endsection
