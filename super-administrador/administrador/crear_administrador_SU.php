<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "datasena_db");
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // ---------------------------
    // Captura de datos
    // ---------------------------
    $tipo_documento = $conn->real_escape_string($_POST['tipo_documento'] ?? '');
    $numero_documento = $conn->real_escape_string($_POST['numero_documento'] ?? '');
    $nombres = $conn->real_escape_string($_POST['nombres'] ?? '');
    $apellidos = $conn->real_escape_string($_POST['apellidos'] ?? '');
    $nickname = $conn->real_escape_string($_POST['nickname'] ?? '');
    $correo_electronico = $conn->real_escape_string($_POST['correo_electronico'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
    $rol_id = isset($_POST['rol_id']) ? (int)$_POST['rol_id'] : 1;

    $erroresCampo = []; // errores asociados a cada campo

    // ---------------------------
    // Validaciones
    // ---------------------------
    $tipos_validos = ['CC', 'TI', 'CE', 'Otro'];
    if (!in_array($tipo_documento, $tipos_validos)) {
        $erroresCampo['tipo_documento'] = "Seleccione un tipo de documento válido.";
    }

    if (!preg_match('/^\d{5,20}$/', $numero_documento)) {
        $erroresCampo['numero_documento'] = "Número de documento entre 5 y 20 dígitos.";
    }

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombres)) {
        $erroresCampo['nombres'] = "Solo letras y espacios.";
    }

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellidos)) {
        $erroresCampo['apellidos'] = "Solo letras y espacios.";
    }

    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $nickname)) {
        $erroresCampo['nickname'] = "Entre 3 y 50 caracteres, solo letras, números y guiones bajos.";
    }

    if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
        $erroresCampo['correo_electronico'] = "Correo electrónico inválido.";
    }

    if (strlen($contrasena) < 6) {
        $erroresCampo['contrasena'] = "Mínimo 6 caracteres.";
    }

    if ($contrasena !== $confirmar_contrasena) {
        $erroresCampo['confirmar_contrasena'] = "Las contraseñas no coinciden.";
    }

    // ---------------------------
    // Verificar duplicados en BD
    // ---------------------------
    if (empty($erroresCampo)) {
        $sql_check = "SELECT id FROM admin WHERE numero_documento=? OR nickname=? OR correo_electronico=?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("sss", $numero_documento, $nickname, $correo_electronico);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $erroresCampo['general'] = "Ya existe un administrador con esos datos.";
        }
        $stmt_check->close();
    }

    // ---------------------------
    // Insertar si todo está bien
    // ---------------------------
    if (empty($erroresCampo)) {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt_insert = $conn->prepare("INSERT INTO admin (tipo_documento, numero_documento, nombres, apellidos, nickname, correo_electronico, contrasena, rol_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("ssssssssi", $tipo_documento, $numero_documento, $nombres, $apellidos, $nickname, $correo_electronico, $hash, $rol_id);

        if ($stmt_insert->execute()) {
            echo "<script>alert('✅ Administrador creado con éxito.'); window.location.href='../super_menu.html';</script>";
            exit;
        } else {
            $erroresCampo['general'] = "Error al crear el administrador.";
        }
        $stmt_insert->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Crear Administrador</title>
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon" />
    <link rel="stylesheet" href="../administrador/crear_administrador_SU.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <style>
        .error-text { color: red; font-size: 0.85em; margin-top: 2px; display: block; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg barra-superior-govco"></nav>
    <h1>DATASENA</h1>
    <img src="../../img/logo-sena.png" alt="Logo de la Empresa" class="img" />
    <div class="forma-container">
        <h3>Crear Administrador</h3>
        <?php if (isset($erroresCampo['general'])): ?>
            <div class="mensaje-error"><?= $erroresCampo['general'] ?></div>
        <?php endif; ?>
        <form method="post" novalidate>
            <div class="forma-grid">
                <!-- Primera columna -->
                <div>
                    <div class="forma-row">
                        <label for="tipo_documento"><i class="fas fa-id-card"></i> Tipo de Documento:</label>
                        <select name="tipo_documento" id="tipo_documento" required class="md-input">
                            <option value="">Seleccione el tipo de documento</option>
                            <option value="CC" <?= (($_POST['tipo_documento'] ?? '') === 'CC') ? 'selected' : '' ?>>Cédula de ciudadanía (CC)</option>
                            <option value="TI" <?= (($_POST['tipo_documento'] ?? '') === 'TI') ? 'selected' : '' ?>>Tarjeta de identidad (TI)</option>
                            <option value="CE" <?= (($_POST['tipo_documento'] ?? '') === 'CE') ? 'selected' : '' ?>>Cédula de extranjería (CE)</option>
                            <option value="Otro" <?= (($_POST['tipo_documento'] ?? '') === 'Otro') ? 'selected' : '' ?>>Otro</option>
                        </select>
                        <?php if (isset($erroresCampo['tipo_documento'])): ?>
                            <small class="error-text"><?= $erroresCampo['tipo_documento'] ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="forma-row">
                        <label for="numero_documento"><i class="fas fa-clipboard-list"></i> Número de documento:</label>
                        <input type="text" name="numero_documento" id="numero_documento"
                            value="<?= htmlspecialchars($_POST['numero_documento'] ?? '') ?>" class="md-input" />
                        <?php if (isset($erroresCampo['numero_documento'])): ?>
                            <small class="error-text"><?= $erroresCampo['numero_documento'] ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="forma-row">
                        <label for="nombres"><i class="fas fa-user"></i> Nombres:</label>
                        <input type="text" name="nombres" id="nombres"
                            value="<?= htmlspecialchars($_POST['nombres'] ?? '') ?>" class="md-input" />
                        <?php if (isset($erroresCampo['nombres'])): ?>
                            <small class="error-text"><?= $erroresCampo['nombres'] ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="forma-row">
                        <label for="apellidos"><i class="fas fa-user-alt"></i> Apellidos:</label>
                        <input type="text" name="apellidos" id="apellidos"
                            value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>" class="md-input" />
                        <?php if (isset($erroresCampo['apellidos'])): ?>
                            <small class="error-text"><?= $erroresCampo['apellidos'] ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="forma-row">
                        <label for="nickname"><i class="fas fa-user-tag"></i> Nickname:</label>
                        <input type="text" name="nickname" id="nickname"
                            value="<?= htmlspecialchars($_POST['nickname'] ?? '') ?>" class="md-input" />
                        <?php if (isset($erroresCampo['nickname'])): ?>
                            <small class="error-text"><?= $erroresCampo['nickname'] ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Segunda columna -->
                <div>
                    <div class="forma-row">
                        <label for="correo_electronico"><i class="fas fa-envelope"></i> Correo electrónico:</label>
                        <input type="email" name="correo_electronico" id="correo_electronico"
                            value="<?= htmlspecialchars($_POST['correo_electronico'] ?? '') ?>" class="md-input" />
                        <?php if (isset($erroresCampo['correo_electronico'])): ?>
                            <small class="error-text"><?= $erroresCampo['correo_electronico'] ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="forma-row">
                        <label for="contrasena"><i class="fas fa-lock"></i> Contraseña:</label>
                        <input type="password" name="contrasena" id="contrasena" class="md-input" />
                        <?php if (isset($erroresCampo['contrasena'])): ?>
                            <small class="error-text"><?= $erroresCampo['contrasena'] ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="forma-row">
                        <label for="confirmar_contrasena"><i class="fas fa-lock"></i> Confirmar contraseña:</label>
                        <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" class="md-input" />
                        <?php if (isset($erroresCampo['confirmar_contrasena'])): ?>
                            <small class="error-text"><?= $erroresCampo['confirmar_contrasena'] ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="forma-row">
                        <label for="rol_id"><i class="fas fa-user-shield"></i> Rol:</label>
                        <select class="md-input" disabled><option value="1" selected>Administrador</option></select>
                        <input type="hidden" name="rol_id" value="1" />
                    </div>
                </div>
            </div>
            <div class="buttons-container">
                <button type="submit" class="back_crear">✅ Crear</button>
                <button type="button" class="back_crear" onclick="window.location.href='../super_menu.html'">↩️ Regresar</button>
            </div>
        </form>
    </div>
    <footer>
        <a>&copy; 2025 Todos los derechos reservados - Proyecto SENA</a>
    </footer>
<nav class="navbar navbar-expand-lg barra-superior-govco"></nav>
</body>
</html>
