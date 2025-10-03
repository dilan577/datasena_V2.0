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
// Establece conexión con MySQL usando los parámetros: servidor, usuario, contraseña y base de datos
$conexion = new mysqli("localhost", "root", "", "datasena_db");

// Verifica si hay errores en la conexión y detiene la ejecución si los hay
if ($conexion->connect_error) {
    die("Error de conexi&oacute;n: " . $conexion->connect_error);
}

// ====================================================================
// INICIALIZACIÓN DE VARIABLES
// ====================================================================
// Array que almacenará los datos de un administrador individual
$admin = [
    'id' => '',
    'tipo_documento' => '',
    'numero_documento' => '',
    'nombres' => '',
    'apellidos' => '',
    'nickname' => '',
    'correo_electronico' => '',
    'contrasena' => '',
    'rol_id' => '',
    'estado_habilitacion' => '',
    'fecha_creacion' => ''
];

// Array que almacenará todos los administradores cuando se solicite mostrarlos
$todos_admins = [];

// Variable para mostrar mensajes de éxito o error al usuario
$mensaje = "";

// ====================================================================
// PROCESAMIENTO DE ACTUALIZACIÓN DE ADMINISTRADOR
// ====================================================================
// Verifica si la petición es POST y si existe el campo 'id' (indica que se va a actualizar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty($_POST['id'])) {
    // Captura el ID del administrador a actualizar
    $id = $_POST['id'];
    
    // Captura los datos del formulario, si no existen asigna null
    $tipo_documento     = $_POST['tipo_documento']     ?? null;
    $numero_documento   = $_POST['numero_documento']   ?? null;
    $nombres            = $_POST['nombres']            ?? null;
    $apellidos          = $_POST['apellidos']          ?? null;
    $nickname           = $_POST['nickname']           ?? null;
    $correo_electronico = $_POST['correo_electronico'] ?? null;
    $contrasena         = $_POST['contrasena']         ?? null;
    $rol_id             = $_POST['rol_id']             ?? null;

    // --- VALIDACIONES ---
    // Valida que el número de documento solo contenga dígitos y sea positivo
    if (!ctype_digit($numero_documento) || $numero_documento <= 0) {
        $mensaje = "❌ El número de documento debe ser positivo y solo contener dígitos.";
    } 
    // Valida que el correo electrónico tenga un formato válido
    elseif (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "❌ El correo electrónico no es válido.";
    } 
    else {
        // --- VERIFICAR DUPLICADOS ---
        // Consulta para verificar si ya existe otro administrador con el mismo documento, nickname o correo
        $sql_check = "SELECT id FROM admin 
                      WHERE (numero_documento=? OR nickname=? OR correo_electronico=?) 
                      AND id<>?";
        
        // Prepara la consulta para prevenir inyección SQL
        $stmt_check = $conexion->prepare($sql_check);
        
        // Vincula los parámetros: 3 strings (documento, nickname, correo) y 1 entero (id)
        $stmt_check->bind_param("sssi", $numero_documento, $nickname, $correo_electronico, $id);
        
        // Ejecuta la consulta
        $stmt_check->execute();
        
        // Almacena el resultado para poder contar las filas
        $stmt_check->store_result();

        // Si encuentra registros duplicados, muestra error
        if ($stmt_check->num_rows > 0) {
            $mensaje = "❌ Ya existe un administrador con ese documento, nickname o correo.";
        } 
        else {
            // --- ACTUALIZACIÓN ---
            // Arrays para construir dinámicamente la consulta UPDATE
            $campos = [];      // Almacenará los campos a actualizar (ej: "nombres=?")
            $tipos = "";       // Almacenará los tipos de datos para bind_param (ej: "sss")
            $parametros = [];  // Almacenará los valores a actualizar

            // Solo agrega a la actualización los campos que no sean null
            if ($tipo_documento !== null) {
                $campos[] = "tipo_documento=?";
                $tipos .= "s";  // 's' indica que es un string
                $parametros[] = $tipo_documento;
            }
            if ($numero_documento !== null) {
                $campos[] = "numero_documento=?";
                $tipos .= "s";
                $parametros[] = $numero_documento;
            }
            if ($nombres !== null) {
                $campos[] = "nombres=?";
                $tipos .= "s";
                $parametros[] = $nombres;
            }
            if ($apellidos !== null) {
                $campos[] = "apellidos=?";
                $tipos .= "s";
                $parametros[] = $apellidos;
            }
            if ($nickname !== null) {
                $campos[] = "nickname=?";
                $tipos .= "s";
                $parametros[] = $nickname;
            }
            if ($correo_electronico !== null) {
                $campos[] = "correo_electronico=?";
                $tipos .= "s";
                $parametros[] = $correo_electronico;
            }
            // Solo actualiza la contraseña si se ingresó una nueva
            if (!empty($contrasena)) {
                $campos[] = "contrasena=?";
                $tipos .= "s";
                // Encripta la contraseña usando el algoritmo por defecto de PHP
                $parametros[] = password_hash($contrasena, PASSWORD_DEFAULT);
            }

            // Si hay campos para actualizar
            if (!empty($campos)) {
                // Agrega el tipo de dato del ID (entero) y el ID a los parámetros
                $tipos .= "i";  // 'i' indica que es un entero
                $parametros[] = $id;

                // Construye la consulta UPDATE dinámicamente
                $sql = "UPDATE admin SET " . implode(", ", $campos) . " WHERE id=?";
                
                // Prepara la consulta
                $stmt = $conexion->prepare($sql);
                
                // Vincula todos los parámetros dinámicamente usando el operador spread
                $stmt->bind_param($tipos, ...$parametros);

                // Ejecuta la actualización y muestra mensaje según el resultado
                $mensaje = $stmt->execute() ? "✅ Administrador actualizado correctamente." : "❌ Error al actualizar el administrador.";
                
                // Cierra el statement
                $stmt->close();
            } else {
                $mensaje = "⚠️ No se enviaron datos para actualizar.";
            }
        }
        // Cierra el statement de verificación
        $stmt_check->close();
    }
}

// ====================================================================
// BÚSQUEDA DE ADMINISTRADOR POR DOCUMENTO
// ====================================================================
// Verifica si se presionó el botón "Buscar"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    // Captura y limpia el número de documento ingresado
    $numero_documento = trim($_POST['numero_documento']);
    
    // Prepara la consulta para buscar por número de documento
    $stmt = $conexion->prepare("SELECT * FROM admin WHERE numero_documento = ?");
    
    // Vincula el parámetro
    $stmt->bind_param("s", $numero_documento);
    
    // Ejecuta la consulta
    $stmt->execute();
    
    // Obtiene el resultado
    $resultado = $stmt->get_result();
    
    // Si encuentra el administrador, lo almacena en $admin; si no, deja el array vacío
    $admin = $resultado->num_rows > 0 ? $resultado->fetch_assoc() : [];
    
    // Si no se encontró ningún administrador, muestra mensaje de error
    if (empty($admin)) $mensaje = "❌ No se encontr&oacute; ning&uacute;n administrador.";
    
    // Cierra el statement
    $stmt->close();
}

// ====================================================================
// MOSTRAR TODOS LOS ADMINISTRADORES
// ====================================================================
// Verifica si se presionó el botón "Mostrar Todos"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mostrar_todos'])) {
    // Consulta para obtener todos los administradores
    $sql = "SELECT * FROM admin";
    
    // Ejecuta la consulta
    $resultado = $conexion->query($sql);
    
    // Si hay resultados
    if ($resultado && $resultado->num_rows > 0) {
        // Recorre todos los registros y los agrega al array $todos_admins
        while ($fila = $resultado->fetch_assoc()) {
            $todos_admins[] = $fila;
        }
    } else {
        // Si no hay administradores registrados, muestra mensaje
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
    <title>Visualizar / Actualizar Administrador</title>
    <!-- Enlace a la hoja de estilos CSS -->
    <link rel="stylesheet" href="../../super-administrador/administrador/actualizar_admin_su_v2.css" />
    <!-- Icono de la página (favicon) -->
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon" />
</head>
<body>

<!-- Barra superior del gobierno colombiano -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<!-- Encabezado principal -->
<h1>DATASENA</h1>
<!-- Logo del SENA -->
<img src="../../img/logo-sena.png" alt="Logo SENA" class="img" />

<!-- Contenedor principal del formulario -->
<div class="form-container">
    <h2>Visualizar / Actualizar Administrador</h2>

    <!-- Muestra mensaje de éxito o error si existe -->
    <?php if (!empty($mensaje)): ?>
        <!-- El color del mensaje depende si contiene el emoji de error (❌) -->
        <p style="color:<?= str_contains($mensaje, '❌') ? 'red' : 'green' ?>; font-weight:bold;"><?= $mensaje ?></p>
    <?php endif; ?>

    <!-- Formulario de búsqueda -->
    <form action="actualizar_admin_su.php" method="post" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <label for="numero_documento">Buscar por Documento:</label>
        <!-- Campo de entrada para el número de documento -->
        <input type="text" name="numero_documento" id="numero_documento" required>
        <!-- Botón para buscar un administrador específico -->
        <button class="logout-btn" type="submit" name="buscar">🔍 Buscar</button>
        <!-- Botón para mostrar todos los administradores -->
        <button class="logout-btn" type="submit" name="mostrar_todos" id="btn-todos">📋 Mostrar Todos</button>
        <!-- Botón para regresar al menú principal -->
        <button class="logout-btn" onclick="window.location.href='../super_menu.html'">↩️ Regresar</button>
    </form>

    <!-- Script para remover la validación "required" cuando se presiona "Mostrar Todos" -->
    <script>
        document.getElementById('btn-todos').addEventListener('click', function () {
            // Permite enviar el formulario sin llenar el campo de documento
            document.getElementById('numero_documento').removeAttribute('required');
        });
    </script>

    <hr>

    <!-- Formulario de actualización - solo se muestra si se encontró un administrador -->
    <?php if (!empty($admin['id'])): ?>
        <form class="form-grid" action="actualizar_admin_su.php" method="post">
            <!-- Campo oculto que envía el ID del administrador -->
            <input type="hidden" name="id" value="<?= htmlspecialchars($admin['id']) ?>">

            <!-- Selector de tipo de documento -->
            <div class="form-row">
                <label>Tipo de documento:</label>
                <select name="tipo_documento" required>
                    <option value="">Seleccione</option>
                    <!-- Cada opción se marca como seleccionada si coincide con el valor actual -->
                    <option value="CC" <?= $admin['tipo_documento'] == 'CC' ? 'selected' : '' ?>>C&eacute;dula</option>
                    <option value="TI" <?= $admin['tipo_documento'] == 'TI' ? 'selected' : '' ?>>Tarjeta</option>
                    <option value="CE" <?= $admin['tipo_documento'] == 'CE' ? 'selected' : '' ?>>C&eacute;dula extranjera</option>
                    <option value="Otro" <?= $admin['tipo_documento'] == 'Otro' ? 'selected' : '' ?>>Otro</option>
                </select>
            </div>

            <!-- Campo para número de documento - solo acepta números -->
            <div class="form-row"><label>Documento:</label>
                <input type="text" name="numero_documento" pattern="[0-9]+" title="Solo n&uacute;meros" value="<?= htmlspecialchars($admin['numero_documento']) ?>" required>
            </div>

            <!-- Campo para nombres - solo acepta letras y espacios -->
            <div class="form-row"><label>Nombres:</label>
                <input type="text" name="nombres" pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+" title="Solo letras" value="<?= htmlspecialchars($admin['nombres']) ?>" required>
            </div>

            <!-- Campo para apellidos - solo acepta letras y espacios -->
            <div class="form-row"><label>Apellidos:</label>
                <input type="text" name="apellidos" pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+" title="Solo letras" value="<?= htmlspecialchars($admin['apellidos']) ?>" required>
            </div>

            <!-- Campo para nickname -->
            <div class="form-row"><label>Nickname:</label>
                <input type="text" name="nickname" value="<?= htmlspecialchars($admin['nickname']) ?>" required>
            </div>

            <!-- Campo para correo electrónico - validación HTML5 automática -->
            <div class="form-row"><label>Correo:</label>
                <input type="email" name="correo_electronico" value="<?= htmlspecialchars($admin['correo_electronico']) ?>" required>
            </div>

            <!-- Campo para contraseña - solo se actualiza si se ingresa un valor -->
            <div class="form-row">
                <label>Contrase&ntilde;a:</label>
                <input type="password" name="contrasena" placeholder="Ingrese nueva contrase&ntilde;a" class="input-estandar">
            </div>
            
            <!-- Campo de rol - muestra el nombre pero es de solo lectura -->
            <div class="form-row">
                <label>Rol:</label>
                <!-- Muestra "Administrador" si rol_id es 1, sino muestra "Usuario" -->
                <input type="text" value="<?= $admin['rol_id'] == 1 ? 'Administrador' : 'Usuario' ?>" readonly>
                <!-- Campo oculto que envía el valor real del rol_id -->
                <input type="hidden" name="rol_id" value="<?= $admin['rol_id'] ?>">
            </div>

            <!-- Campo de fecha de creación - solo lectura -->
            <div class="form-row"><label>Fecha de Creaci&oacute;n:</label>
                <input type="text" value="<?= htmlspecialchars($admin['fecha_creacion']) ?>" readonly>
            </div>

            <!-- Botón para enviar el formulario de actualización -->
            <div class="form-row botones-finales">
                <button class="logout-btn" type="submit">Actualizar</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- Tabla de todos los administradores - solo se muestra si se solicitó "Mostrar Todos" -->
    <?php if (!empty($todos_admins)): ?>
        <h3>&#128195; Administradores registrados</h3>
        <!-- Contenedor con scroll horizontal para tablas anchas -->
        <div style="overflow-x:auto;">
            <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; background:#fff;">
                <!-- Encabezados de la tabla -->
                <thead style="background-color:#0078c0; color:white;">
                    <tr>
                        <th>Tipo Doc</th>
                        <th>Documento</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Nickname</th>
                        <th>Correo</th>
                        <th>Habilitaci&oacute;n</th>
                        <th>Creaci&oacute;n</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Recorre el array de administradores y crea una fila por cada uno -->
                    <?php foreach ($todos_admins as $a): ?>
                        <tr>
                            <!-- htmlspecialchars previene ataques XSS al escapar caracteres especiales -->
                            <td><?= htmlspecialchars($a['tipo_documento']) ?></td>
                            <td><?= htmlspecialchars($a['numero_documento']) ?></td>
                            <td><?= htmlspecialchars($a['nombres']) ?></td>
                            <td><?= htmlspecialchars($a['apellidos']) ?></td>
                            <td><?= htmlspecialchars($a['nickname']) ?></td>
                            <td><?= htmlspecialchars($a['correo_electronico']) ?></td>
                            <td><?= htmlspecialchars($a['estado_habilitacion']) ?></td>
                            <td><?= htmlspecialchars($a['fecha_creacion']) ?></td>
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
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra inferior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<!-- Script de validación adicional para nombres y apellidos -->
<script>
// Busca el formulario de actualización y le agrega un evento de validación
document.querySelector("form.form-grid")?.addEventListener("submit", function(e) {
    // Expresión regular que solo acepta letras, espacios y caracteres acentuados
    const letras = /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/;
    
    // Obtiene los campos de nombres y apellidos
    const nombres = document.querySelector("[name='nombres']");
    const apellidos = document.querySelector("[name='apellidos']");

    // Valida que ambos campos solo contengan letras
    if (!letras.test(nombres.value) || !letras.test(apellidos.value)) {
        // Muestra alerta y previene el envío del formulario
        alert("Nombres y apellidos solo deben contener letras.");
        e.preventDefault();
    }
});
</script>
</body>
</html>