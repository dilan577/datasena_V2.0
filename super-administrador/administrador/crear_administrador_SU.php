<?php
session_start();

// Validar que esté logueado y que sea superadministrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'super') {
    header("Location: ../inicio_sesion.html");
    exit();
}
// ====================================================================
// PROCESAMIENTO DEL FORMULARIO DE CREACIÓN DE ADMINISTRADOR
// ====================================================================
// Verifica si la petición es de tipo POST (cuando se envía el formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ====================================================================
    // CONEXIÓN A LA BASE DE DATOS
    // ====================================================================
    // Establece conexión con MySQL usando: servidor, usuario, contraseña y nombre de base de datos
    $conn = new mysqli("localhost", "root", "", "datasena_db");
    
    // Verifica si hubo un error en la conexión y detiene la ejecución si es así
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // ====================================================================
    // CAPTURA DE DATOS DEL FORMULARIO
    // ====================================================================
    // Captura y escapa cada campo del formulario para prevenir inyección SQL
    // El operador ?? devuelve string vacío si el campo no existe
    $tipo_documento = $conn->real_escape_string($_POST['tipo_documento'] ?? '');
    $numero_documento = $conn->real_escape_string($_POST['numero_documento'] ?? '');
    $nombres = $conn->real_escape_string($_POST['nombres'] ?? '');
    $apellidos = $conn->real_escape_string($_POST['apellidos'] ?? '');
    $nickname = $conn->real_escape_string($_POST['nickname'] ?? '');
    $correo_electronico = $conn->real_escape_string($_POST['correo_electronico'] ?? '');
    
    // Las contraseñas no se escapan aquí porque se van a hashear después
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
    
    // Captura el rol_id y lo convierte a entero, si no existe usa 1 por defecto
    $rol_id = isset($_POST['rol_id']) ? (int)$_POST['rol_id'] : 1;

    // Array asociativo para almacenar los errores de validación por campo
    $erroresCampo = [];

    // ====================================================================
    // VALIDACIONES DE DATOS
    // ====================================================================
    
    // --- Validación del tipo de documento ---
    // Define los tipos de documento permitidos
    $tipos_validos = ['CC', 'TI', 'CE', 'Otro'];
    // Verifica si el tipo seleccionado está en el array de tipos válidos
    if (!in_array($tipo_documento, $tipos_validos)) {
        $erroresCampo['tipo_documento'] = "Seleccione un tipo de documento válido.";
    }

    // --- Validación del número de documento ---
    // Debe contener entre 5 y 20 dígitos numéricos
    // ^ inicio de cadena, \d dígito, {5,20} cantidad entre 5 y 20, $ fin de cadena
    if (!preg_match('/^\d{5,20}$/', $numero_documento)) {
        $erroresCampo['numero_documento'] = "Número de documento entre 5 y 20 dígitos.";
    }

    // --- Validación de nombres ---
    // Solo debe contener letras (con acentos), ñ y espacios
    // u al final activa el modo Unicode para soportar acentos
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombres)) {
        $erroresCampo['nombres'] = "Solo letras y espacios.";
    }

    // --- Validación de apellidos ---
    // Misma regla que nombres: solo letras, acentos, ñ y espacios
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellidos)) {
        $erroresCampo['apellidos'] = "Solo letras y espacios.";
    }

    // --- Validación del nickname ---
    // Debe tener entre 3 y 50 caracteres
    // Solo letras (sin acentos), números y guiones bajos
    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $nickname)) {
        $erroresCampo['nickname'] = "Entre 3 y 50 caracteres, solo letras, números y guiones bajos.";
    }

    // --- Validación del correo electrónico ---
    // Usa el filtro nativo de PHP para validar formato de email
    if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
        $erroresCampo['correo_electronico'] = "Correo electrónico inválido.";
    }

    // --- Validación de la contraseña ---
    // Debe tener al menos 6 caracteres
    if (strlen($contrasena) < 6) {
        $erroresCampo['contrasena'] = "Mínimo 6 caracteres.";
    }

    // --- Validación de confirmación de contraseña ---
    // Verifica que ambas contraseñas sean idénticas
    if ($contrasena !== $confirmar_contrasena) {
        $erroresCampo['confirmar_contrasena'] = "Las contraseñas no coinciden.";
    }

    // ====================================================================
    // VERIFICACIÓN DE DUPLICADOS EN LA BASE DE DATOS
    // ====================================================================
    // Solo verifica duplicados si no hay errores de validación previos
    if (empty($erroresCampo)) {
        // Consulta para verificar si ya existe un admin con el mismo documento, nickname o correo
        $sql_check = "SELECT id FROM admin WHERE numero_documento=? OR nickname=? OR correo_electronico=?";
        
        // Prepara la consulta para prevenir inyección SQL
        $stmt_check = $conn->prepare($sql_check);
        
        // Vincula los tres parámetros como strings
        $stmt_check->bind_param("sss", $numero_documento, $nickname, $correo_electronico);
        
        // Ejecuta la consulta
        $stmt_check->execute();
        
        // Almacena el resultado para poder contar las filas
        $stmt_check->store_result();
        
        // Si encuentra algún registro, significa que ya existe un admin con esos datos
        if ($stmt_check->num_rows > 0) {
            $erroresCampo['general'] = "Ya existe un administrador con esos datos.";
        }
        
        // Cierra el statement de verificación
        $stmt_check->close();
    }

    // ====================================================================
    // INSERCIÓN EN LA BASE DE DATOS
    // ====================================================================
    // Solo inserta si no hay errores de validación ni duplicados
    if (empty($erroresCampo)) {
        // Encripta la contraseña usando el algoritmo por defecto de PHP (bcrypt)
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        
        // Define el estado por defecto del administrador
        $estado_habilitacion = "Activo";

        // Prepara la consulta INSERT con placeholders (?)
        $stmt_insert = $conn->prepare("
            INSERT INTO admin 
            (tipo_documento, numero_documento, nombres, apellidos, nickname, correo_electronico, contrasena, rol_id, estado_habilitacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Vincula los parámetros:
        // s = string, i = integer
        // sssssssis = 8 strings y 1 integer
        $stmt_insert->bind_param(
            "sssssssis",           // Tipos de datos
            $tipo_documento,       // 1. string
            $numero_documento,     // 2. string
            $nombres,              // 3. string
            $apellidos,            // 4. string
            $nickname,             // 5. string
            $correo_electronico,   // 6. string
            $hash,                 // 7. string (contraseña hasheada)
            $rol_id,               // 8. integer
            $estado_habilitacion   // 9. string
        );

        // Ejecuta la inserción
        if ($stmt_insert->execute()) {
            // Si tiene éxito, muestra alerta y redirige al menú principal
            echo "<script>alert('✅ Administrador creado con éxito.'); window.location.href='../super_menu.html';</script>";
            exit; // Detiene la ejecución del script
        } else {
            // Si falla, guarda el error en el array de errores
            $erroresCampo['general'] = "Error al crear el administrador: " . $stmt_insert->error;
        }
        
        // Cierra el statement de inserción
        $stmt_insert->close();
    }

    // Cierra la conexión a la base de datos
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Crear Administrador</title>
    <!-- Favicon de la página -->
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon" />
    <!-- Hoja de estilos personalizada -->
    <link rel="stylesheet" href="../administrador/crear_administrador_SU.css" />
    <!-- Librería de iconos Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    
    <!-- Estilos inline para mensajes de error -->
    <style>
        /* Estilo para mensajes de error de cada campo */
        .error-text { 
            color: red; 
            font-size: 0.85em; 
            margin-top: 2px; 
            display: block; 
        }
        /* Estilo para mensaje de error general */
        .mensaje-error { 
            color: red; 
            font-weight: bold; 
            margin-bottom: 10px; 
        }
    </style>
</head>
<body>
<!-- Barra superior del gobierno colombiano -->
<nav class="navbar navbar-expand-lg barra-superior-govco"></nav>

    <!-- Encabezado principal -->
    <h1>DATASENA</h1>
    
    <!-- Logo del SENA -->
    <img src="../../img/logo-sena.png" alt="Logo de la Empresa" class="img" />
    
    <!-- Contenedor principal del formulario -->
    <div class="forma-container">
        <h3>Crear Administrador</h3>
        
        <!-- Muestra mensaje de error general si existe -->
        <?php if (isset($erroresCampo['general'])): ?>
            <div class="mensaje-error"><?= $erroresCampo['general'] ?></div>
        <?php endif; ?>
        
        <!-- 
            Formulario de creación de administrador
            novalidate: desactiva la validación HTML5 nativa para usar validación personalizada
        -->
        <form method="post" novalidate>
            <!-- Grid de dos columnas para organizar los campos -->
            <div class="forma-grid">
                
                <!-- ====================================================================
                     PRIMERA COLUMNA - Datos de identificación
                     ==================================================================== -->
                <div>
                    <!-- Campo: Tipo de documento -->
                    <div class="forma-row">
                        <label for="tipo_documento">
                            <i class="fas fa-id-card"></i> Tipo de Documento:
                        </label>
                        <select name="tipo_documento" id="tipo_documento" required class="md-input">
                            <option value="">Seleccione el tipo de documento</option>
                            <!-- Mantiene la opción seleccionada después de enviar el formulario -->
                            <option value="CC" <?= (($_POST['tipo_documento'] ?? '') === 'CC') ? 'selected' : '' ?>>Cédula de ciudadanía (CC)</option>
                            <option value="TI" <?= (($_POST['tipo_documento'] ?? '') === 'TI') ? 'selected' : '' ?>>Tarjeta de identidad (TI)</option>
                            <option value="CE" <?= (($_POST['tipo_documento'] ?? '') === 'CE') ? 'selected' : '' ?>>Cédula de extranjería (CE)</option>
                            <option value="Otro" <?= (($_POST['tipo_documento'] ?? '') === 'Otro') ? 'selected' : '' ?>>Otro</option>
                        </select>
                        <!-- Muestra error específico de este campo si existe -->
                        <?php if (isset($erroresCampo['tipo_documento'])): ?>
                            <small class="error-text"><?= $erroresCampo['tipo_documento'] ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Campo: Número de documento -->
                    <div class="forma-row">
                        <label for="numero_documento">
                            <i class="fas fa-clipboard-list"></i> Número de documento:
                        </label>
                        <!-- htmlspecialchars previene ataques XSS al mostrar el valor ingresado -->
                        <input type="text" name="numero_documento" id="numero_documento"
                            value="<?= htmlspecialchars($_POST['numero_documento'] ?? '') ?>" class="md-input" />
                        <!-- Muestra error específico si existe -->
                        <?php if (isset($erroresCampo['numero_documento'])): ?>
                            <small class="error-text"><?= $erroresCampo['numero_documento'] ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Campo: Nombres -->
                    <div class="forma-row">
                        <label for="nombres">
                            <i class="fas fa-user"></i> Nombres:
                        </label>
                        <input type="text" name="nombres" id="nombres"
                            value="<?= htmlspecialchars($_POST['nombres'] ?? '') ?>" class="md-input" />
                        <?php if (isset($erroresCampo['nombres'])): ?>
                            <small class="error-text"><?= $erroresCampo['nombres'] ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Campo: Apellidos -->
                    <div class="forma-row">
                        <label for="apellidos">
                            <i class="fas fa-user-alt"></i> Apellidos:
                        </label>
                        <input type="text" name="apellidos" id="apellidos"
                            value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>" class="md-input" />
                        <?php if (isset($erroresCampo['apellidos'])): ?>
                            <small class="error-text"><?= $erroresCampo['apellidos'] ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Campo: Nickname -->
                    <div class="forma-row">
                        <label for="nickname">
                            <i class="fas fa-user-tag"></i> Nickname:
                        </label>
                        <input type="text" name="nickname" id="nickname"
                            value="<?= htmlspecialchars($_POST['nickname'] ?? '') ?>" class="md-input" />
                        <?php if (isset($erroresCampo['nickname'])): ?>
                            <small class="error-text"><?= $erroresCampo['nickname'] ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- ====================================================================
                     SEGUNDA COLUMNA - Datos de acceso y seguridad
                     ==================================================================== -->
                <div>
                    <!-- Campo: Correo electrónico -->
                    <div class="forma-row">
                        <label for="correo_electronico">
                            <i class="fas fa-envelope"></i> Correo electrónico:
                        </label>
                        <input type="email" name="correo_electronico" id="correo_electronico"
                            value="<?= htmlspecialchars($_POST['correo_electronico'] ?? '') ?>" class="md-input" />
                        <?php if (isset($erroresCampo['correo_electronico'])): ?>
                            <small class="error-text"><?= $erroresCampo['correo_electronico'] ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Campo: Contraseña -->
                    <div class="forma-row">
                        <label for="contrasena">
                            <i class="fas fa-lock"></i> Contraseña:
                        </label>
                        <!-- No se mantiene el valor por seguridad -->
                        <input type="password" name="contrasena" id="contrasena" class="md-input" />
                        <?php if (isset($erroresCampo['contrasena'])): ?>
                            <small class="error-text"><?= $erroresCampo['contrasena'] ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Campo: Confirmar contraseña -->
                    <div class="forma-row">
                        <label for="confirmar_contrasena">
                            <i class="fas fa-lock"></i> Confirmar contraseña:
                        </label>
                        <!-- Tampoco se mantiene el valor por seguridad -->
                        <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" class="md-input" />
                        <?php if (isset($erroresCampo['confirmar_contrasena'])): ?>
                            <small class="error-text"><?= $erroresCampo['confirmar_contrasena'] ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Campo: Rol (solo lectura) -->
                    <div class="forma-row">
                        <label for="rol_id">
                            <i class="fas fa-user-shield"></i> Rol:
                        </label>
                        <!-- Select deshabilitado para mostrar el rol pero no permitir cambios -->
                        <select class="md-input" disabled>
                            <option value="1" selected>Administrador</option>
                        </select>
                        <!-- Campo oculto que envía el valor real del rol al servidor -->
                        <input type="hidden" name="rol_id" value="1" />
                    </div>
                </div>
            </div>
            
            <!-- ====================================================================
                 BOTONES DE ACCIÓN
                 ==================================================================== -->
            <div class="buttons-container">
                <!-- Botón para enviar el formulario -->
                <button type="submit" class="back_crear">✅ Crear</button>
                <!-- Botón para regresar al menú sin guardar -->
                <button type="button" class="back_crear" onclick="window.location.href='../super_menu.php'">↩️ Regresar</button>
            </div>
        </form>
    </div>
    
    <!-- Pie de página -->
    <footer>
        <a>&copy; 2025 Todos los derechos reservados - Proyecto SENA</a>
    </footer>

<!-- Barra inferior del gobierno colombiano -->
<nav class="navbar navbar-expand-lg barra-superior-govco"></nav>
</body>
</html>