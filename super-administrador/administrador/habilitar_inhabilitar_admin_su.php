<?php
// ====================================================================
// CONEXIÓN A LA BASE DE DATOS
// ====================================================================
// Establece conexión con MySQL usando los parámetros: servidor, usuario, contraseña y base de datos
$conexion = new mysqli("localhost", "root", "", "datasena_db");

// Verifica si hay errores en la conexión y detiene la ejecución si los hay
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// ====================================================================
// INICIALIZACIÓN DE VARIABLES
// ====================================================================
// Variable para almacenar los datos de un administrador individual buscado
$admin = null;

// Array para almacenar todos los administradores cuando se solicite mostrarlos
$todos = [];

// Variable para almacenar mensajes de éxito o error
$mensaje = "";

// Variable para identificar el tipo de mensaje (éxito o error) y aplicar estilos CSS
$mensaje_tipo = "";

// ====================================================================
// PROCESAMIENTO DE ACTUALIZACIÓN DE ESTADO
// ====================================================================
// Verifica si la petición es POST y si se presionó el botón "actualizar_estado"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_estado'])) {
    // Captura el número de documento y el nuevo estado, asigna string vacío si no existen
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nuevo_estado = $_POST['nuevo_estado'] ?? '';
    
    // Verifica que ambos campos tengan valor antes de proceder
    if (!empty($numero_documento) && !empty($nuevo_estado)) {
        // Prepara la consulta UPDATE para cambiar el estado de habilitación
        $stmt = $conexion->prepare("UPDATE admin SET estado_habilitacion = ? WHERE numero_documento = ?");
        
        // Vincula los parámetros: nuevo estado y número de documento (ambos strings)
        $stmt->bind_param("ss", $nuevo_estado, $numero_documento);
        
        // Ejecuta la actualización y verifica si afectó alguna fila
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            // Si se actualizó correctamente, muestra mensaje de éxito
            $mensaje = "✅ Estado actualizado correctamente.";
            $mensaje_tipo = "exito";
        } else {
            // Si no se encontró el administrador o no hubo cambios, muestra advertencia
            $mensaje = "⚠️ No se encontró el administrador o no hubo cambios.";
            $mensaje_tipo = "error";
        }
        
        // Cierra el statement
        $stmt->close();
    }
}

// ====================================================================
// BÚSQUEDA INDIVIDUAL DE ADMINISTRADOR
// ====================================================================
// Verifica si es una petición GET con número de documento pero sin la opción "mostrar_todos"
// Esto significa que el usuario está buscando un administrador específico
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['numero_documento']) && !isset($_GET['mostrar_todos'])) {
    // Captura el número de documento desde la URL
    $numero_documento = $_GET['numero_documento'];
    
    // Prepara la consulta para buscar el administrador por su número de documento
    $stmt = $conexion->prepare("SELECT * FROM admin WHERE numero_documento = ?");
    
    // Vincula el parámetro (string)
    $stmt->bind_param("s", $numero_documento);
    
    // Ejecuta la consulta
    $stmt->execute();
    
    // Obtiene el resultado
    $result = $stmt->get_result();
    
    // Almacena el administrador encontrado (o null si no existe)
    $admin = $result->fetch_assoc();
    
    // Cierra el statement
    $stmt->close();
    
    // Si no se encontró ningún administrador, muestra mensaje de error
    if (!$admin) {
        $mensaje = "❌ Administrador no encontrado.";
        $mensaje_tipo = "error";
    }
}

// ====================================================================
// MOSTRAR TODOS LOS ADMINISTRADORES
// ====================================================================
// Verifica si es una petición GET y si se presionó el botón "mostrar_todos"
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['mostrar_todos'])) {
    // Consulta para obtener todos los registros de la tabla admin
    $sql = "SELECT * FROM admin";
    
    // Ejecuta la consulta
    $result = $conexion->query($sql);
    
    // Si hay resultados
    if ($result && $result->num_rows > 0) {
        // Recorre todos los registros y los agrega al array $todos
        while ($fila = $result->fetch_assoc()) {
            $todos[] = $fila;
        }
    } else {
        // Si no hay administradores registrados, muestra mensaje de error
        $mensaje = "❌ No hay administradores registrados.";
        $mensaje_tipo = "error";
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
    <title>Habilitar/Inhabilitar Administrador</title>
    <!-- Favicon de la página -->
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
    <!-- Hoja de estilos personalizada -->
    <link rel="stylesheet" href="habilitar_admin.css">

</head>
<body>

<!-- Barra superior del gobierno colombiano -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

    <!-- Título principal de la aplicación -->
    <h1>DATASENA</h1>
    
    <!-- Encabezado de la página con logo y título -->
    <header>
        <img src="../../img/logo-sena.png" alt="Logo SENA" class="img">
        <h1>Panel de Habilitación de Administradores</h1>
    </header>
    
    <!-- Contenido principal de la página -->
    <main>
        <!-- ====================================================================
             FORMULARIO DE BÚSQUEDA
             ==================================================================== -->
        <div class="search-box">
            <!-- Formulario que usa método GET para búsquedas -->
            <form method="get">
                <label for="buscar_doc">Número de Documento:</label>
                <!-- Campo de entrada para el número de documento a buscar -->
                <input type="text" id="buscar_doc" name="numero_documento">
                <!-- Botón para buscar un administrador específico -->
                <button type="submit">🔍 Buscar</button>
                <!-- Botón para mostrar todos los administradores -->
                <button type="submit" name="mostrar_todos">📋 Mostrar Todos</button>
                <!-- Botón para regresar al menú principal -->
                <button type="button" class="logout-btn" onclick="window.location.href='../super_menu.html'">↩️ Regresar</button>
            </form>
        </div>
        
        <!-- ====================================================================
             VISUALIZACIÓN DE MENSAJES
             ==================================================================== -->
        <!-- Muestra mensaje de éxito o error si existe -->
        <?php if (!empty($mensaje)): ?>
            <!-- La clase CSS depende del tipo de mensaje (exito o error) -->
            <div class="mensaje <?= $mensaje_tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        
        <!-- ====================================================================
             SECCIÓN: DATOS DEL ADMINISTRADOR INDIVIDUAL
             ==================================================================== -->
        <!-- Solo se muestra si se encontró un administrador específico -->
        <?php if ($admin): ?>
            <section class="empresa-detalle">
                <h2>Datos del Administrador</h2>
                <!-- Lista con la información del administrador -->
                <ul>
                    <!-- Muestra nombre completo concatenando nombres y apellidos -->
                    <li><strong>Nombre completo:</strong> <?= htmlspecialchars($admin['nombres'] . ' ' . $admin['apellidos']) ?></li>
                    <!-- Número de documento -->
                    <li><strong>Documento:</strong> <?= htmlspecialchars($admin['numero_documento']) ?></li>
                    <!-- Correo electrónico -->
                    <li><strong>Correo electrónico:</strong> <?= htmlspecialchars($admin['correo_electronico']) ?></li>
                    <!-- Nickname -->
                    <li><strong>Nickname:</strong> <?= htmlspecialchars($admin['nickname']) ?></li>
                    <!-- Fecha en que se creó el registro -->
                    <li><strong>Fecha de creación:</strong> <?= htmlspecialchars($admin['fecha_creacion']) ?></li>
                    <!-- Estado actual con emoji visual (✅ si está activo, ❌ si está inactivo) -->
                    <li><strong>Estado actual:</strong> <?= $admin['estado_habilitacion'] === 'Activo' ? '✅ Habilitado' : '❌ Inhabilitado' ?></li>
                </ul>
                
                <!-- Formulario para cambiar el estado del administrador -->
                <form method="post" class="form-estado">
                    <!-- Campo oculto que envía el número de documento al servidor -->
                    <input type="hidden" name="numero_documento" value="<?= htmlspecialchars($admin['numero_documento']) ?>">
                    
                    <label for="nuevo_estado">Cambiar Estado:</label>
                    <!-- Selector para elegir el nuevo estado -->
                    <select name="nuevo_estado" required>
                        <option value="">Seleccione...</option>
                        <!-- Opción para habilitar (Activo) -->
                        <option value="Activo">✅ Habilitar</option>
                        <!-- Opción para inhabilitar (Inactivo) -->
                        <option value="Inactivo">❌ Inhabilitar</option>
                    </select>
                    
                    <!-- Botón para enviar el formulario y actualizar el estado -->
                    <div class="botones">
                        <button type="submit" name="actualizar_estado">Actualizar</button>
                    </div>
                </form>
            </section>
        <?php endif; ?>
        
        <!-- ====================================================================
             SECCIÓN: TABLA DE TODOS LOS ADMINISTRADORES
             ==================================================================== -->
        <!-- Solo se muestra si el array $todos contiene datos -->
        <?php if (!empty($todos)): ?>
            <h3>📋 Lista de Administradores Registrados</h3>
                <div class="tabla-contenedor">
                    <!-- Contenedor con scroll horizontal para tablas anchas -->
                    <div style="overflow-x:auto;">
                        <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; background: #fff;">
                            <!-- Encabezados de la tabla con fondo azul -->
                            <thead style="background-color: #0078c0; color: white;">
                                <tr>
                                    <th>Tipo Doc.</th>
                                    <th>Número de Documento</th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>Correo Electrónico</th>
                                    <th>Nickname</th>
                                    <th>Estado</th>
                                    <th>Fecha de Creación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Recorre el array de administradores y crea una fila por cada uno -->
                                <?php foreach ($todos as $a): ?>
                                    <tr>
                                        <!-- htmlspecialchars previene ataques XSS al escapar caracteres especiales -->
                                        <td><?= htmlspecialchars($a['tipo_documento']) ?></td>
                                        <td><?= htmlspecialchars($a['numero_documento']) ?></td>
                                        <td><?= htmlspecialchars($a['nombres']) ?></td>
                                        <td><?= htmlspecialchars($a['apellidos']) ?></td>
                                        <td><?= htmlspecialchars($a['correo_electronico']) ?></td>
                                        <td><?= htmlspecialchars($a['nickname']) ?></td>
                                        <td><?= htmlspecialchars($a['estado_habilitacion']) ?></td>
                                        <td><?= htmlspecialchars($a['fecha_creacion']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
        <?php endif; ?>

    </main>
    
    <!-- Pie de página -->
    <footer>
        <p>&copy; 2025 Todos los derechos reservados - Proyecto SENA</p>
    </footer>
    
    <!-- Barra inferior del gobierno colombiano -->
    <nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
    <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
    </nav>
</body>
</html>