<?php
<<<<<<< HEAD
session_start();

// Validar que esté logueado y que sea superadministrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'empresa') {
    header("Location: ../inicio_sesion.html");
    exit();
}   
=======
// Establecemos el tipo de codificación para evitar problemas con caracteres especiales (tildes, ñ, etc.).
// Aunque no es obligatorio aquí, es buena práctica.
header('Content-Type: text/html; charset=utf-8');

// Intentamos conectar a la base de datos usando PDO (más seguro y moderno que MySQLi procedural).
// Base de datos: datasena_db | Usuario: root | Sin contraseña (común en entornos locales).
>>>>>>> 0343bb3c639e5d2debaf61b3be41b959940f6f09
try {
    $conexion = new PDO("mysql:host=localhost;dbname=datasena_db;charset=utf8", "root", "");
    // Configuramos PDO para que lance excepciones en caso de error (mejor control de errores).
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si falla la conexión, mostramos un mensaje de error y detenemos la ejecución.
    // ⚠️ En producción, evita mostrar $e->getMessage() por seguridad.
    die("Error de conexión: " . htmlspecialchars($e->getMessage()));
}

// Inicializamos variables para manejar resultados y mensajes.
$programa = null;        // No se usa actualmente, pero podría usarse si se muestra un solo programa por ID.
$programas = [];         // Almacenará los programas encontrados (uno o varios).
$mensaje = "";           // Mensaje informativo o de error para el usuario.

// Verificamos si la solicitud es de tipo POST (es decir, se envió el formulario).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Si el usuario hizo clic en "Mostrar Todos"
    if (isset($_POST['mostrar_todos'])) {
        // Consultamos todos los programas ordenados por ID ascendente.
        $stmt = $conexion->query("SELECT id, nombre_programa, tipo_programa, numero_ficha, duracion_programa, activacion FROM programas ORDER BY id ASC");
        $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si no hay registros, mostramos un mensaje.
        if (empty($programas)) {
            $mensaje = "❌ No hay programas registrados.";
        }
    
    // Si el usuario hizo clic en "Buscar"
    } elseif (isset($_POST['buscar'])) {
        // Obtenemos y limpiamos el término de búsqueda.
        $busqueda = trim($_POST['nombre_buscar']);
        
        // Validamos que no esté vacío.
        if ($busqueda === '') {
            $mensaje = "⚠️ Por favor ingrese un término para buscar.";
        } else {
            // Preparamos una consulta segura con LIKE para buscar en nombre o tipo de programa.
            // Usamos comodines (%) para coincidencias parciales.
            $sql = "SELECT id, nombre_programa, tipo_programa, numero_ficha, duracion_programa, activacion 
                    FROM programas 
                    WHERE nombre_programa LIKE :busqueda OR tipo_programa LIKE :busqueda";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':busqueda' => "%$busqueda%"]);
            $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si no hay resultados, lo indicamos.
            if (empty($programas)) {
                $mensaje = "⚠️ No se encontraron programas con ese criterio.";
            }
        }
    }
}

// Cerramos la conexión explícitamente (opcional, pero buena práctica).
$conexion = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Programas</title>
    <!-- Enlace al archivo CSS personalizado -->
    <link rel="stylesheet" href="../../administrador/admin_programas_formacion/admin_listar_programa.css" />
    <!-- Favicon del sistema -->
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon" />
</head>
<body>

<!-- Barra superior del Gobierno de Colombia (GOV.CO) -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<!-- Encabezado con título y logo del SENA -->
<header>
    <h1>DATASENA</h1>
    <img src="../../img/logo-sena.png" alt="Logo SENA" class="img" />
</header>

<!-- Contenedor principal del formulario y resultados -->
<div class="form-container">
    <h2>Listar Programas</h2>

    <!-- Mostramos un mensaje si existe (éxito, advertencia o error) -->
    <?php if (!empty($mensaje)): ?>
        <p class="mensaje-error"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <!-- Formulario de búsqueda y acciones -->
    <form method="POST" action="" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
        <label for="nombre_buscar">Buscar programa:</label>
        <input
            type="text"
            id="nombre_buscar"
            name="nombre_buscar"
            placeholder="Nombre o tipo de programa"
            value="<?= isset($_POST['nombre_buscar']) ? htmlspecialchars($_POST['nombre_buscar']) : '' ?>"
            <?= isset($_POST['mostrar_todos']) ? '' : 'required' ?>
        />
        <!-- Botón para buscar por término -->
        <button class="logout-btn" type="submit" name="buscar">🔍 Buscar</button>
        <!-- Botón para mostrar todos los programas (omite la validación 'required') -->
        <button class="logout-btn" type="submit" name="mostrar_todos" onclick="document.getElementById('nombre_buscar').removeAttribute('required');">📋 Mostrar Todos</button>
        <!-- Botón para regresar al menú principal -->
        <button type="button" class="logout-btn" onclick="window.location.href='../empresa_menu.php'">↩️ Regresar</button>
    </form>

    <hr />

    <!-- Mostramos los resultados si existen -->
    <?php if (!empty($programas)): ?>
        <?php if (count($programas) === 1): ?>
            <!-- Si solo hay un resultado, lo mostramos en formato de tarjeta vertical (más legible) -->
            <?php $p = $programas[0]; ?>
            <div class="programa-card">
                <p><strong>ID:</strong> <?= htmlspecialchars($p['id']) ?></p>
                <p><strong>Nombre del Programa:</strong> <?= htmlspecialchars($p['nombre_programa']) ?></p>
                <p><strong>Tipo de Programa:</strong> <?= htmlspecialchars($p['tipo_programa']) ?></p>
                <p><strong>Número de Ficha:</strong> <?= htmlspecialchars($p['numero_ficha']) ?></p>
                <p><strong>Duración:</strong> <?= htmlspecialchars($p['duracion_programa']) ?></p>
                <p><strong>Activación:</strong> <?= htmlspecialchars($p['activacion']) ?></p>
            </div>
        <?php else: ?>
            <!-- Si hay múltiples resultados, los mostramos en una tabla -->
            <h3>📋 Programas Registrados</h3>
            <div class="user-list" style="overflow-x:auto;">
                <table border="1" cellpadding="6" cellspacing="0">
                    <thead style="background-color: #0078c0; color: white;">
                        <tr>
                            <th>Nombre del Programa</th>
                            <th>Tipo de Programa</th>
                            <th>Número de Ficha</th>
                            <th>Duración</th>
                            <th>Activación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programas as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['nombre_programa']) ?></td>
                                <td><?= htmlspecialchars($p['tipo_programa']) ?></td>
                                <td><?= htmlspecialchars($p['numero_ficha']) ?></td>
                                <td><?= htmlspecialchars($p['duracion_programa']) ?></td>
                                <td><?= htmlspecialchars($p['activacion']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Pie de página -->
<footer>
    <a>&copy; 2025 Todos los derechos reservados - Proyecto SENA</a>
</footer>

<!-- Barra inferior del Gobierno de Colombia (GOV.CO) -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
    <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

</body>
</html>