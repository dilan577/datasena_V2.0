<?php
session_start();

// Validar que esté logueado y que sea superadministrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'super') {
    header("Location: ../inicio_sesion.html");
    exit();
}
// ====================================================================
// CONEXIÓN A LA BASE DE DATOS
// ====================================================================
// Crea la conexión a la base de datos usando MySQLi
// Parámetros: servidor, usuario, contraseña, nombre de base de datos
$conexion = new mysqli("localhost", "root", "", "datasena_db");

// Verifica si hay error de conexión y detiene el script si existe
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// ====================================================================
// INICIALIZACIÓN DE VARIABLES
// ====================================================================
// $admin -> guardará los datos de un administrador buscado individualmente
$admin = null;

// $todos_admins -> array que guardará todos los administradores si se solicita listar todos
$todos_admins = [];

// $mensaje -> guardará mensajes de error, advertencia o información
$mensaje = "";

// ====================================================================
// BÚSQUEDA INDIVIDUAL DE ADMINISTRADOR
// ====================================================================
// Se ejecuta si el método es POST y hay un dato de búsqueda
// y NO se está solicitando "buscar todos"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dato_busqueda']) && !isset($_POST['buscar_todos'])) {
    
    // Captura y limpia el dato de búsqueda (eliminando espacios al inicio y final)
    $dato = trim($_POST['dato_busqueda']);
    
    // Consulta SQL preparada para buscar por número de documento o nickname
    $sql = "SELECT 
                tipo_documento,         -- Tipo de documento (CC, TI, CE, Otro)
                numero_documento,       -- Número de documento
                nombres,                -- Nombres del administrador
                apellidos,              -- Apellidos del administrador
                nickname,               -- Nickname o usuario
                correo_electronico,     -- Correo electrónico
                estado_habilitacion,    -- Estado de habilitación
                fecha_creacion          -- Fecha de registro
            FROM admin
            WHERE numero_documento = ? OR nickname = ?";
    
    // Prepara la consulta para prevenir inyección SQL
    $stmt = $conexion->prepare($sql);
    
    // Verifica que la preparación haya sido exitosa
    if (!$stmt) {
        die("Error al preparar la consulta: " . $conexion->error);
    }
    
    // Vincula los parámetros a la consulta
    // "ss" indica que ambos son strings
    $stmt->bind_param("ss", $dato, $dato);
    
    // Ejecuta la consulta
    $stmt->execute();
    
    // Obtiene el resultado
    $resultado = $stmt->get_result();
    
    // Verifica si se encontró algún administrador
    if ($resultado->num_rows > 0) {
        // Toma el primer resultado encontrado
        $admin = $resultado->fetch_assoc();
    } else {
        // Si no se encontró, muestra mensaje de advertencia
        $mensaje = "⚠️ No se encontró ningún administrador con ese número de documento o nickname.";
    }
    
    // Cierra el statement
    $stmt->close();
}

// ====================================================================
// LISTAR TODOS LOS ADMINISTRADORES
// ====================================================================
// Se ejecuta si el método es POST y se presionó el botón "buscar_todos"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_todos'])) {

    // Consulta SQL que trae todos los administradores
    $sql = "SELECT 
                tipo_documento,
                numero_documento,
                nombres,
                apellidos,
                nickname,
                correo_electronico,
                estado_habilitacion,
                fecha_creacion
            FROM admin";

    // Ejecuta la consulta
    $resultado = $conexion->query($sql);

    // Si hay resultados, los agrega al array $todos_admins
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $todos_admins[] = $fila;
        }
    } else {
        // Si no hay registros, muestra mensaje informativo
        $mensaje = "❌ No hay administradores registrados.";
    }
}

// Cierra la conexión a la base de datos
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Administradores</title>
    <!-- Hoja de estilos personalizada -->
    <link rel="stylesheet" href="listar_admin_SU_v2.css">
    <!-- Favicon de la página -->
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">

</head>
<body>

<!-- ==================================================================== 
     BARRA SUPERIOR DEL GOBIERNO COLOMBIANO
     ==================================================================== -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<!-- ==================================================================== 
     ENCABEZADO DE LA PÁGINA
     ==================================================================== -->
<header>
    <h1>DATASENA</h1>
    <img src="../../img/logo-sena.png" alt="Logo SENA" class="img">
</header>

<!-- ==================================================================== 
     CONTENEDOR PRINCIPAL
     ==================================================================== -->
<div class="form-container">
    <h2>Listar Administradores</h2>
    
    <!-- ====================================================================
         MENSAJES DE ERROR O INFORMACIÓN
         ==================================================================== -->
    <?php if (!empty($mensaje)): ?>
        <p class="mensaje-error"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>
    
    <!-- ====================================================================
         FORMULARIO DE BÚSQUEDA
         ==================================================================== -->
    <form action="listar_admin_SU.php" method="post" style="display:flex; gap:10px; flex-wrap: wrap; align-items: center;">
        
        <!-- Campo de búsqueda -->
        <label for="buscar_dato">Buscar administrador:</label>
        <input type="text" 
               id="buscar_dato" 
               name="dato_busqueda" 
               placeholder="Número de documento o nickname" 
               required>
        
        <!-- Botón buscar individual -->
        <button class="logout-btn" type="submit">🔍 Buscar</button>
        
        <!-- Botón mostrar todos -->
        <button class="logout-btn" 
                type="submit" 
                name="buscar_todos" 
                onclick="document.getElementById('buscar_dato').removeAttribute('required')">
            📋 Mostrar Todos
        </button>
        
        <!-- Botón regresar -->
        <button type="button" 
                class="logout-btn" 
                onclick="window.location.href='../super_menu.php'">
            ↩️ Regresar
        </button>
    </form>
    
    <hr>
    
    <!-- ====================================================================
         SECCIÓN: ADMINISTRADOR INDIVIDUAL
         ==================================================================== -->
    <?php if ($admin): ?>
        <div class="empresa-card">
            <p><strong>Tipo de documento:</strong> <?= htmlspecialchars($admin['tipo_documento']) ?></p>
            <p><strong>Número de documento:</strong> <?= htmlspecialchars($admin['numero_documento']) ?></p>
            <p><strong>Nombres:</strong> <?= htmlspecialchars($admin['nombres']) ?></p>
            <p><strong>Apellidos:</strong> <?= htmlspecialchars($admin['apellidos']) ?></p>
            <p><strong>Nickname:</strong> <?= htmlspecialchars($admin['nickname']) ?></p>
            <p><strong>Correo electrónico:</strong> <?= htmlspecialchars($admin['correo_electronico']) ?></p>
            <p><strong>Estado de habilitación:</strong> <?= htmlspecialchars($admin['estado_habilitacion']) ?></p>
            <p><strong>Fecha de creación:</strong> <?= htmlspecialchars($admin['fecha_creacion'] ?? 'Sin fecha') ?></p>
        </div>
    <?php endif; ?>
    
    <!-- ====================================================================
         SECCIÓN: LISTA DE TODOS LOS ADMINISTRADORES
         ==================================================================== -->
    <?php if (!empty($todos_admins)): ?>
        <h3>📋 Lista de Administradores Registrados</h3>
        <div style="overflow-x:auto;">
            <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; background:#fff;">
                <thead style="background-color: #0078c0; color: white;">
                    <tr>
                        <th>Tipo de Documento</th>
                        <th>Número de Documento</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Nickname</th>
                        <th>Correo Electrónico</th>
                        <th>Estado de Habilitación</th>
                        <th>Fecha de Creación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todos_admins as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['tipo_documento']) ?></td>
                            <td><?= htmlspecialchars($a['numero_documento']) ?></td>
                            <td><?= htmlspecialchars($a['nombres']) ?></td>
                            <td><?= htmlspecialchars($a['apellidos']) ?></td>
                            <td><?= htmlspecialchars($a['nickname']) ?></td>
                            <td><?= htmlspecialchars($a['correo_electronico']) ?></td>
                            <td><?= htmlspecialchars($a['estado_habilitacion'] ?? 'N/D') ?></td>
                            <td><?= htmlspecialchars($a['fecha_creacion'] ?? 'Sin fecha') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ====================================================================
     PIE DE PÁGINA
     ==================================================================== -->
<footer>
    <p style="font-size:0.8em;">&copy; 2025 Todos los derechos reservados - Proyecto SENA</p>
</footer>

<!-- BARRA INFERIOR DEL GOBIERNO -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra inferior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

</body>
</html>