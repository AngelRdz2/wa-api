@extends('layouts.app')

@section('content')
<div class="container mt-5 max-w-4xl mx-auto p-6 bg-white rounded shadow">

    <h2 class="text-2xl font-bold mb-6">Enviar mensajes masivos por categoría</h2>
    <p class="text-gray-600 mb-4">Paso final: Seleccione el número de WhatsApp (instancia) que realizará el envío para su área.</p>
    
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
        // Recuperamos $categorias de la sesión y $instances del controlador
        $categorias = session('numeros_por_categoria', []);
    @endphp

    @if(empty($categorias))
        <div class="p-3 bg-yellow-100 text-yellow-700 rounded">
            No hay números cargados. <a href="{{ route('excel.upload') }}" class="underline text-blue-600 hover:text-blue-800">Sube un archivo Excel primero.</a>
        </div>
    @else
        <form method="POST" action="{{ route('messages.send') }}">
            @csrf

            {{-- SELECTOR DE INSTANCIA --}}
            <div class="mb-6 p-4 border border-blue-200 rounded bg-blue-50">
                <label for="whatsapp_instance_id" class="block font-bold mb-2 text-blue-800">
                    📞 Seleccionar Número de Envío (Instancia)
                </label>
                <select 
                    name="whatsapp_instance_id" 
                    id="whatsapp_instance_id" 
                    class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    required
                >
                    <option value="">-- Elija la instancia de WhatsApp --</option>
                    @foreach($instances as $instance)
                        <option value="{{ $instance->id }}" 
                            {{ old('whatsapp_instance_id') == $instance->id ? 'selected' : '' }}>
                            {{ $instance->name }} (Área: {{ $instance->area }}) - Teléfono: {{ $instance->phone}}
                        </option>
                    @endforeach
                </select>
                @error('whatsapp_instance_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            {{-- FIN SELECTOR DE INSTANCIA --}}


            <h3 class="text-xl font-semibold mt-8 mb-4 border-b pb-2">Resumen de Plantillas a Enviar:</h3>
            
            {{-- Resumen de Plantillas (sin textarea) --}}
            @foreach($categorias as $categoria => $numeros)
                <div class="mb-4 p-3 bg-gray-50 border border-gray-200 rounded">
                    <p class="font-bold text-lg text-indigo-600">{{ $categoria }}</p>
                    <p class="text-gray-600">Se enviará el mensaje de plantilla para **{{ $categoria }}** a **{{ count($numeros) }}** clientes.</p>
                </div>
            @endforeach

            
            <div class="flex gap-4 mt-6">
                
                {{-- BOTÓN DE CANCELAR / VOLVER (NUEVO) --}}
                <a href="{{ route('messages-preview') }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 transition font-bold">
                    ⬅️ Volver a Vista Previa
                </a>

                {{-- BOTÓN DE ENVÍO --}}
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition font-bold">
                    🚀 Enviar mensajes
                </button>
            </div>
        </form>
    @endif

</div>
@endsection