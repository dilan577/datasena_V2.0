<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "datasena_db");
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Recibir y limpiar datos
    $tipo_documento = $conn->real_escape_string($_POST['tipo_documento'] ?? '');
    $numero_documento = $conn->real_escape_string($_POST['numero_documento'] ?? '');
    $nombres = $conn->real_escape_string($_POST['nombres'] ?? '');
    $apellidos = $conn->real_escape_string($_POST['apellidos'] ?? '');
    $nickname = $conn->real_escape_string($_POST['nickname'] ?? '');
    $correo_electronico = $conn->real_escape_string($_POST['correo_electronico'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
    $rol_id = isset($_POST['rol_id']) ? (int)$_POST['rol_id'] : 1;

    $errores = [];

    // Validaciones
    $tipos_validos = ['CC', 'TI', 'CE', 'Otro'];
    if (!in_array($tipo_documento, $tipos_validos)) {
        $errores[] = "Tipo de documento inválido.";
    }

    if (!preg_match('/^\d{5,20}$/', $numero_documento)) {
        $errores[] = "Número de documento debe tener entre 5 y 20 dígitos.";
    }

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombres)) {
        $errores[] = "Los nombres solo pueden contener letras y espacios.";
    }

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellidos)) {
        $errores[] = "Los apellidos solo pueden contener letras y espacios.";
    }

    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $nickname)) {
        $errores[] = "El nickname debe tener entre 3 y 50 caracteres, solo letras, números y guiones bajos.";
    }

    if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Correo electrónico inválido.";
    }

    if (strlen($contrasena) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres.";
    }

    if ($contrasena !== $confirmar_contrasena) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    if (empty($errores)) {
        // Verificar que no exista usuario con esos datos
        $sql_check = "SELECT id FROM admin WHERE numero_documento=? OR nickname=? OR correo_electronico=?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("sss", $numero_documento, $nickname, $correo_electronico);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "❌ Ya existe un administrador con esos datos.";
        } else {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmt_insert = $conn->prepare("INSERT INTO admin (tipo_documento, numero_documento, nombres, apellidos, nickname, correo_electronico, contrasena, rol_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssssssi", $tipo_documento, $numero_documento, $nombres, $apellidos, $nickname, $correo_electronico, $hash, $rol_id);

            if ($stmt_insert->execute()) {
                $success = "✅ Administrador creado con éxito.";
            } else {
                $error = "❌ Error al crear el administrador.";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    } else {
        $error = "❌ " . implode("<br>❌ ", $errores);
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
</head>
<body>
    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo" />
    </div>
    <h1>DATASENA</h1>
    <img src="../../img/logo-sena.png" alt="Logo de la Empresa" class="img" />

    <div class="forma-container">
        <h3>Crear Administrador</h3>
        <?php if (isset($success)): ?>
            <div class="mensaje-exito"><?= htmlspecialchars($success) ?></div>
        <?php elseif (isset($error)): ?>
            <div class="mensaje-error"><?= $error ?></div>
        <?php endif; ?>
        <form method="post" novalidate>
            <div class="forma-grid">
                <!-- Primera columna -->
                <div>
                    <div class="forma-row">
                        <label for="tipo_documento"><i class="fas fa-id-card"></i> Tipo de Documento:</label>
                        <select name="tipo_documento" id="tipo_documento" required class="md-input" >
                            <option value="">Seleccione el tipo de documento</option>
                            <option value="CC" <?= (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] === 'CC') ? 'selected' : '' ?>>Cédula de ciudadanía (CC)</option>
                            <option value="TI" <?= (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] === 'TI') ? 'selected' : '' ?>>Tarjeta de identidad (TI)</option>
                            <option value="CE" <?= (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] === 'CE') ? 'selected' : '' ?>>Cédula de extranjería (CE)</option>
                            <option value="Otro" <?= (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] === 'Otro') ? 'selected' : '' ?>>Otro</option>
                        </select>
                    </div>

                    <div class="forma-row">
                        <label for="numero_documento"><i class="fas fa-clipboard-list"></i> Número de documento:</label>
                        <input
                            type="text"
                            name="numero_documento"
                            id="numero_documento"
                            placeholder="Ingrese el número de documento"
                            required
                            class="md-input"
                            maxlength="20"
                            value="<?= isset($_POST['numero_documento']) ? htmlspecialchars($_POST['numero_documento']) : '' ?>"
                            pattern="\d{5,20}"
                            title="Solo números entre 5 y 20 dígitos"
                        />
                    </div>

                    <div class="forma-row">
                        <label for="nombres"><i class="fas fa-user"></i> Nombres:</label>
                        <input
                            type="text"
                            name="nombres"
                            id="nombres"
                            placeholder="Ingrese los nombres"
                            required
                            class="md-input"
                            maxlength="100"
                            value="<?= isset($_POST['nombres']) ? htmlspecialchars($_POST['nombres']) : '' ?>"
                            pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                            title="Solo letras y espacios"
                        />
                    </div>

                    <div class="forma-row">
                        <label for="apellidos"><i class="fas fa-user-alt"></i> Apellidos:</label>
                        <input
                            type="text"
                            name="apellidos"
                            id="apellidos"
                            placeholder="Ingrese los apellidos"
                            required
                            class="md-input"
                            maxlength="100"
                            value="<?= isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : '' ?>"
                            pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                            title="Solo letras y espacios"
                        />
                    </div>

                    <div class="forma-row">
                        <label for="nickname"><i class="fas fa-user-tag"></i> Nickname (usuario):</label>
                        <input
                            type="text"
                            name="nickname"
                            id="nickname"
                            placeholder="Ingrese el nickname"
                            required
                            class="md-input"
                            maxlength="50"
                            value="<?= isset($_POST['nickname']) ? htmlspecialchars($_POST['nickname']) : '' ?>"
                            pattern="[A-Za-z0-9_]{3,50}"
                            title="3 a 50 caracteres, letras, números y guiones bajos"
                        />
                    </div>
                </div>

                <!-- Segunda columna -->
                <div>
                    <div class="forma-row">
                        <label for="correo_electronico"><i class="fas fa-envelope"></i> Correo electrónico:</label>
                        <input
                            type="email"
                            name="correo_electronico"
                            id="correo_electronico"
                            placeholder="Ingrese el correo electrónico"
                            required
                            class="md-input"
                            maxlength="100"
                            value="<?= isset($_POST['correo_electronico']) ? htmlspecialchars($_POST['correo_electronico']) : '' ?>"
                        />
                    </div>

                    <div class="forma-row">
                        <label for="contrasena"><i class="fas fa-lock"></i> Contraseña:</label>
                        <input
                            type="password"
                            name="contrasena"
                            id="contrasena"
                            placeholder="Ingrese la contraseña"
                            required
                            class="md-input"
                            minlength="6"
                        />
                    </div>

                    <div class="forma-row">
                        <label for="confirmar_contrasena"><i class="fas fa-lock"></i> Confirmar contraseña:</label>
                        <input
                            type="password"
                            name="confirmar_contrasena"
                            id="confirmar_contrasena"
                            placeholder="Confirme la contraseña"
                            required
                            class="md-input"
                            minlength="6"
                        />
                    </div>

                    <div class="forma-row">
                        <label for="rol_id"><i class="fas fa-user-shield"></i> Administrador:</label>
                        <select class="md-input" disabled>
                            <option value="1" selected>Administrador</option>
                        </select>
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

    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo" />
    </div>
</body>
</html>
