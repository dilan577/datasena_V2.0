<?php
// ====================================================================
// CONEXIÓN A LA BASE DE DATOS
// ====================================================================
// Establece conexión con MySQL usando: servidor, usuario, contraseña y base de datos
$conexion = new mysqli("localhost", "root", "", "datasena_db");

// Verifica si hay errores en la conexión y detiene la ejecución si los hay
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// ====================================================================
// INICIALIZACIÓN DE VARIABLES
// ====================================================================
// Variable para almacenar los datos de un administrador individual encontrado
$admin = null;

// Array para almacenar todos los administradores cuando se solicite listarlos
$todos_admins = [];

// Variable para mostrar mensajes de error o advertencia al usuario
$mensaje = "";

// ====================================================================
// BÚSQUEDA INDIVIDUAL DE ADMINISTRADOR
// ====================================================================
// Verifica si es una petición POST con dato de búsqueda pero sin la opción "buscar_todos"
// Esto significa que el usuario está buscando un administrador específico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dato_busqueda']) && !isset($_POST['buscar_todos'])) {
    // Captura el dato de búsqueda y elimina espacios al inicio y final
    $dato = trim($_POST['dato_busqueda']);
    
    // Consulta SQL que busca por número de documento O por nickname
    // Permite buscar usando cualquiera de estos dos campos
    $sql = "SELECT 
                tipo_documento,         -- Tipo de documento (CC, TI, CE, Otro)
                numero_documento,       -- Número de documento
                nombres,                -- Nombres del administrador
                apellidos,              -- Apellidos del administrador
                nickname,               -- Nickname o nombre de usuario
                correo_electronico,     -- Email del administrador
                estado_habilitacion,    -- Estado: Activo o Inactivo
                fecha_creacion          -- Fecha de registro
            FROM admin
            WHERE numero_documento = ? OR nickname = ?";  -- Busca por documento O nickname
    
    // Prepara la consulta para prevenir inyección SQL
    $stmt = $conexion->prepare($sql);
    
    // Verifica si la preparación de la consulta fue exitosa
    if (!$stmt) {
        die("Error al preparar la consulta: " . $conexion->error);
    }
    
    // Vincula los dos parámetros (el mismo dato se usa para ambos campos de búsqueda)
    // "ss" indica que ambos parámetros son strings
    $stmt->bind_param("ss", $dato, $dato);
    
    // Ejecuta la consulta
    $stmt->execute();
    
    // Obtiene el resultado de la consulta
    $resultado = $stmt->get_result();
    
    // Verifica si se encontró algún administrador
    if ($resultado->num_rows > 0) {
        // Si existe, obtiene los datos como un array asociativo
        $admin = $resultado->fetch_assoc();
    } else {
        // Si no se encuentra, muestra mensaje de advertencia
        $mensaje = "⚠️ No se encontró ningún administrador con ese número de documento o nickname.";
    }
    
    // Cierra el statement para liberar recursos
    $stmt->close();
}

// ====================================================================
// LISTAR TODOS LOS ADMINISTRADORES
// ====================================================================
// Verifica si es una petición POST y si se presionó el botón "buscar_todos"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_todos'])) {
    // Consulta SQL para obtener TODOS los administradores de la base de datos
    $sql = "SELECT 
                tipo_documento,         -- Tipo de documento
                numero_documento,       -- Número de documento
                nombres,                -- Nombres
                apellidos,              -- Apellidos
                nickname,               -- Nickname
                correo_electronico,     -- Email
                estado_habilitacion,    -- Estado de habilitación
                fecha_creacion          -- Fecha de creación
            FROM admin";                -- No tiene WHERE, trae todos los registros
    
    // Ejecuta la consulta directamente (sin parámetros, no necesita prepared statement)
    $resultado = $conexion->query($sql);
    
    // Verifica si hay resultados
    if ($resultado && $resultado->num_rows > 0) {
        // Recorre todos los registros y los agrega al array $todos_admins
        while ($fila = $resultado->fetch_assoc()) {
            $todos_admins[] = $fila;
        }
    } else {
        // Si no hay administradores registrados, muestra mensaje de error
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
    <title>Listar Administradores</title>
    <!-- Hoja de estilos personalizada -->
    <link rel="stylesheet" href="listar_admin_SU_v2.css">
    <!-- Favicon de la página -->
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
</head>
<body>

<!-- Barra superior del gobierno colombiano -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<!-- Encabezado de la página -->
<header>
    <h1>DATASENA</h1>
    <img src="../../img/logo-sena.png" alt="Logo SENA" class="img">
</header>

<!-- Contenedor principal -->
<div class="form-container">
    <h2>Listar Administradores</h2>
    
    <!-- ====================================================================
         VISUALIZACIÓN DE MENSAJES DE ERROR O ADVERTENCIA
         ==================================================================== -->
    <!-- Muestra mensaje si existe (error o advertencia) -->
    <?php if (!empty($mensaje)): ?>
        <p class="mensaje-error"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>
    
    <!-- ====================================================================
         FORMULARIO DE BÚSQUEDA
         ==================================================================== -->
    <form action="listar_admin_SU.php" method="post" style="display:flex; gap:10px; flex-wrap: wrap; align-items: center;">
        <label for="buscar_dato">Buscar administrador:</label>
        
        <!-- Campo de entrada para búsqueda por documento o nickname -->
        <input type="text" 
               id="buscar_dato" 
               name="dato_busqueda" 
               placeholder="Número de documento o nickname" 
               required>
        
        <!-- Botón para buscar un administrador específico -->
        <button class="logout-btn" type="submit">🔍 Buscar</button>
        
        <!-- Botón para mostrar todos los administradores -->
        <!-- El onclick remueve el atributo 'required' para permitir enviar el formulario sin llenar el campo -->
        <button class="logout-btn" 
                type="submit" 
                name="buscar_todos" 
                onclick="document.getElementById('buscar_dato').removeAttribute('required')">
            📋 Mostrar Todos
        </button>
        
        <!-- Botón para regresar al menú principal -->
        <button type="button" 
                class="logout-btn" 
                onclick="window.location.href='../super_menu.html'">
            ↩️ Regresar
        </button>
    </form>
    
    <hr>
    
    <!-- ====================================================================
         SECCIÓN: DATOS DE ADMINISTRADOR INDIVIDUAL
         ==================================================================== -->
    <!-- Solo se muestra si se encontró un administrador específico -->
    <?php if ($admin): ?>
        <div class="empresa-card">
            <!-- Muestra cada campo del administrador encontrado -->
            <!-- htmlspecialchars previene ataques XSS al escapar caracteres especiales -->
            <p><strong>Tipo de documento:</strong> <?= htmlspecialchars($admin['tipo_documento']) ?></p>
            <p><strong>Número de documento:</strong> <?= htmlspecialchars($admin['numero_documento']) ?></p>
            <p><strong>Nombres:</strong> <?= htmlspecialchars($admin['nombres']) ?></p>
            <p><strong>Apellidos:</strong> <?= htmlspecialchars($admin['apellidos']) ?></p>
            <p><strong>Nickname:</strong> <?= htmlspecialchars($admin['nickname']) ?></p>
            <p><strong>Correo electrónico:</strong> <?= htmlspecialchars($admin['correo_electronico']) ?></p>
            <p><strong>Estado de habilitación:</strong> <?= htmlspecialchars($admin['estado_habilitacion']) ?></p>
            
            <!-- Si la fecha de creación existe la muestra, sino muestra "Sin fecha" -->
            <p><strong>Fecha de creación:</strong> <?= htmlspecialchars($admin['fecha_creacion'] ?? 'Sin fecha') ?></p>
        </div>
    <?php endif; ?>
    
    <!-- ====================================================================
         SECCIÓN: TABLA DE TODOS LOS ADMINISTRADORES
         ==================================================================== -->
    <!-- Solo se muestra si el array $todos_admins contiene datos -->
    <?php if (!empty($todos_admins)): ?>
        <h3>📋 Lista de Administradores Registrados</h3>
        
        <!-- Contenedor con scroll horizontal para tablas anchas en pantallas pequeñas -->
        <div style="overflow-x:auto;">
            <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; background:#fff;">
                
                <!-- Encabezados de la tabla con fondo azul institucional -->
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
                
                <!-- Cuerpo de la tabla -->
                <tbody>
                    <!-- Recorre el array de administradores y crea una fila por cada uno -->
                    <?php foreach ($todos_admins as $a): ?>
                        <tr>
                            <!-- Muestra cada campo del administrador -->
                            <!-- htmlspecialchars protege contra ataques XSS -->
                            <td><?= htmlspecialchars($a['tipo_documento']) ?></td>
                            <td><?= htmlspecialchars($a['numero_documento']) ?></td>
                            <td><?= htmlspecialchars($a['nombres']) ?></td>
                            <td><?= htmlspecialchars($a['apellidos']) ?></td>
                            <td><?= htmlspecialchars($a['nickname']) ?></td>
                            <td><?= htmlspecialchars($a['correo_electronico']) ?></td>
                            
                            <!-- Verifica si el campo existe antes de mostrarlo, sino muestra 'N/D' -->
                            <td><?= isset($a['estado_habilitacion']) ? htmlspecialchars($a['estado_habilitacion']) : 'N/D' ?></td>
                            
                            <!-- Verifica si la fecha existe antes de mostrarlo, sino muestra 'Sin fecha' -->
                            <td><?= isset($a['fecha_creacion']) ? htmlspecialchars($a['fecha_creacion']) : 'Sin fecha' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Pie de página -->
<footer>
    <a>&copy; 2025 Todos los derechos reservados - Proyecto SENA</a>
</footer>

<!-- Barra inferior del gobierno colombiano -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

</body>
</html>