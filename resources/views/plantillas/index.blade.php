<h2>Crear nueva plantilla</h2>
@if(session('success'))
    <p style="color: green">{{ session('success') }}</p>
@endif
<form action="{{ route('templates.store') }}" method="POST">
    @csrf
    <label>Clasificación de mora:</label>
    <select name="moratorium_classification_id" required>
        @foreach($clasificaciones as $clasificacion)
            <option value="{{ $clasificacion->id }}">{{ $clasificacion->name }}</option>
        @endforeach
    </select><br>

    <label>Nombre de la plantilla:</label>
    <input type="text" name="template_name" required><br>

    <label>Mensaje:</label>
    <textarea name="template" rows="4" required placeholder="Ej: Hola {nombre}, debes {factura} por ${monto}"></textarea><br>

    <button type="submit">Guardar plantilla</button>
</form>

<h2>Plantillas existentes</h2>
<ul>
@foreach($plantillas as $plantilla)
    <li>
        <strong>{{ $plantilla->template_name }}</strong> - {{ $plantilla->classification->name }}<br>
        {{ $plantilla->template }}
    </li>
@endforeach
</ul>
