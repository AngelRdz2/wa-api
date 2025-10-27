@extends('layouts.app')

@section('content')
<div class="container mx-auto px-6 py-8">
    <h1 class="text-3xl font-bold text-green-800 mb-6 border-b pb-2">📨 Vista Previa de Mensajes</h1>

    @if(session('status'))
        <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 mb-6 rounded shadow-sm">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-6 rounded shadow-sm">
            @foreach($errors->all() as $error)
                <p>⚠️ {{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if(empty($mensajesPorCategoria))
        <div class="text-gray-600 mb-4">No hay mensajes para mostrar.</div>
        <a href="{{ route('excel.upload') }}" class="inline-block bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700 transition">
            ⬆️ Volver a Subir Excel
        </a>
    @else
        {{-- 1. ELIMINAMOS LA ETIQUETA <form> Y @csrf --}}
        
        @foreach($mensajesPorCategoria as $categoria => $mensajes)
            <div class="mb-8 bg-white border border-green-200 rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-green-700 mb-4">{{ $categoria }} ({{ count($mensajes) }} mensajes)</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left border border-green-300">
                        <thead class="bg-green-100 text-green-800">
                            <tr>
                                <th class="px-4 py-2 border-b">📱 Número</th>
                                <th class="px-4 py-2 border-b">💬 Mensaje</th>
                            </tr>
                        </thead>
                            <tbody class="divide-y divide-green-100">
                                @foreach($mensajes as $msg)
                                    <tr class="hover:bg-green-50">
                                        <td class="px-4 py-2">{{ $msg['numero'] }}</td>
                                        <td class="px-4 py-2">{{ $msg['mensaje'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <div class="flex flex-wrap gap-4 mt-6">
                {{-- 2. CAMBIO CLAVE: El botón de envío se convierte en un ENLACE --}}
              
                <a href="{{ route('messages.send.form') }}" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition font-bold">
                    👉 Ir a Selección de Instancia y Enviar
                </a>
                
                <a href="{{ route('excel.upload') }}" class="bg-gray-200 text-green-800 px-6 py-2 rounded hover:bg-gray-300 transition">
                    🔄 Volver a Subir Excel
                </a>
            </div>
            
        {{-- ELIMINAMOS EL CIERRE DE LA ETIQUETA </form> --}}
    @endif
</div>
@endsection