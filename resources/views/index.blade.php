<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mensajes WAAPI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Enviar mensaje</h4>
                    <!-- Botón Cerrar Sesión -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">Cerrar sesión</button>
                    </form>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('send.message') }}">
                        @csrf
                        <div class="mb-3">
                            {{--<label for="phone" class="form-label">Número</label>
                            <input type="text" id="phone" name="phone" class="form-control" required>--}}
                        </div>
                        <div class="mb-3">
                            {{--<label for="message" class="form-label">Mensaje</label>
                            <textarea id="message" name="message" class="form-control" rows="4" required></textarea>--}}
                        </div>
                        <button type="submit" class="btn btn-success w-100">Enviar</button>
                    </form>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- Formulario para subir archivo Excel -->
<section id="subir-excel" class="mb-6">
    <h2 class="text-xl font-bold mb-2">📤 Subir archivo Excel con números</h2>
    <form action="{{ route('subir.excel') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="excel" accept=".xlsx,.xls,.csv" class="mb-2">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
            Cargar números
        </button>
    </form>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
