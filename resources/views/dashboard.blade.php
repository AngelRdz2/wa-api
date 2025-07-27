<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        body {
            overflow-x: hidden;
            background: #f0f7f6; /* color de fondo suave */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        #sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 220px;
            background-color: #075E54; /* verde WhatsApp */
            padding-top: 60px;
            transition: transform 0.3s ease;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }
        #sidebar.collapsed {
            transform: translateX(-220px);
        }
        #sidebar a {
            display: block;
            color: white;
            padding: 15px 25px;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: background-color 0.2s, border-left-color 0.2s;
        }
        #sidebar a:hover {
            background-color: #128C7E;
            border-left-color: #25D366;
        }
        #content {
            margin-left: 220px;
            padding: 40px 20px;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        #content.expanded {
            margin-left: 0;
        }
        #menu-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            background-color: #075E54;
            border: none;
            color: white;
            padding: 10px 18px;
            cursor: pointer;
            z-index: 1000;
            border-radius: 6px;
            font-size: 1.2rem;
            font-weight: 700;
        }
        #menu-btn:hover {
            background-color: #128C7E;
        }
        h1 {
            font-size: 3rem;
            color: #075E54;
            margin-bottom: 0.3rem;
        }
        p {
            font-size: 1.25rem;
            color: #333;
            max-width: 450px;
        }
        /* Imagen decorativa */
        .welcome-image {
            margin-top: 40px;
            max-width: 300px;
            opacity: 0.85;
        }
        /* Responsive */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-220px);
            }
            #sidebar.collapsed {
                transform: translateX(0);
            }
            #content {
                margin-left: 0;
                padding: 20px 15px;
            }
            #content.expanded {
                margin-left: 220px;
            }
        }
    </style>
</head>
<body>

<button id="menu-btn">☰ Menú</button>

<nav id="sidebar" class="collapsed">
    <a href="{{ route('dashboard') }}">Inicio</a>
    <a href="{{ route('excel.upload') }}">Subir Excel</a>
    <a href="{{ route('messages.send') }}">Enviar Mensajes</a>
    <a href="{{ route('messages.responses') }}">Ver Respuestas</a>
    <a href="{{ route('profile.edit') }}">Perfil</a>
    <form method="POST" action="{{ route('logout') }}" class="mt-4 px-3">
        @csrf
        <button type="submit" class="btn btn-light w-100 fw-bold">Cerrar sesión</button>
    </form>
</nav>

<div id="content" class="expanded">
    <h1>Bienvenido</h1>
    <p>Gestiona tus envíos masivos de WhatsApp de manera sencilla y eficiente.</p>
    <img
      src="https://cdn-icons-png.flaticon.com/512/124/124034.png"
      alt="WhatsApp"
      class="welcome-image"
    />
</div>

<script>
    const menuBtn = document.getElementById('menu-btn');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');

    menuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        content.classList.toggle('expanded');
    });
</script>

</body>
</html>
