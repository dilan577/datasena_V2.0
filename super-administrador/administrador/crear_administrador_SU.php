<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "123456", "datasena_db");
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    $tipo_documento = $conn->real_escape_string($_POST['tipo_documento'] ?? '');
    $numero_documento = $conn->real_escape_string($_POST['numero_documento'] ?? '');
    $nombres = $conn->real_escape_string($_POST['nombres'] ?? '');
    $apellidos = $conn->real_escape_string($_POST['apellidos'] ?? '');
    $nickname = $conn->real_escape_string($_POST['nickname'] ?? '');
    $correo_electronico = $conn->real_escape_string($_POST['correo_electronico'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
    $rol_id = isset($_POST['rol_id']) ? (int)$_POST['rol_id'] : 1;

    if (
        empty($tipo_documento) || empty($numero_documento) || empty($nombres) || empty($apellidos) ||
        empty($nickname) || empty($correo_electronico) || empty($contrasena) || empty($confirmar_contrasena)
    ) {
        $error = "❌ Todos los campos son obligatorios.";
    } elseif ($contrasena !== $confirmar_contrasena) {
        $error = "❌ Las contraseñas no coinciden.";
    } else {
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
    <link rel="icon" href="../../../img/Logotipo_Datasena.png" type="image/x-icon" />
    <link rel="stylesheet" href=".././/administrador/crear_administrador_SU.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->
</head>
<body>
    <div class="barra-gov">
  <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
</div>
    <h1>DATASENA</h1>
    <img src="../../img/logo-sena.png" alt="Logo de la Empresa" class="img" />

    <div class="forma-container">
        <h3>Crear Administrador</h3>
        <?php if (isset($success)): ?>
            <div class="mensaje-exito"><?= htmlspecialchars($success) ?></div>
        <?php elseif (isset($error)): ?>
            <div class="mensaje-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="forma-grid">
                <!-- Primera columna -->
                <div>
                    <div class="forma-row">
                        <label for="tipo_documento"><i class="fas fa-id-card"></i> Tipo de Documento:</label>
                        <select name="tipo_documento" id="tipo_documento" required class="md-input">
                            <option value="">Seleccione el tipo de documento</option>
                            <option value="CC">Cédula de ciudadanía (CC)</option>
                            <option value="TI">Tarjeta de identidad (TI)</option>
                            <option value="CE">Cédula de extranjería (CE)</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="forma-row">
                        <label for="numero_documento"><i class="fas fa-clipboard-list"></i> Número de documento:</label>
                        <input type="text" name="numero_documento" id="numero_documento" placeholder="Ingrese el número de documento" required class="md-input" maxlength="20" />
                    </div>

                    <div class="forma-row">
                        <label for="nombres"><i class="fas fa-user"></i> Nombres:</label>
                        <input type="text" name="nombres" id="nombres" placeholder="Ingrese los nombres" required class="md-input" maxlength="100" />
                    </div>

                    <div class="forma-row">
                        <label for="apellidos"><i class="fas fa-user-alt"></i> Apellidos:</label>
                        <input type="text" name="apellidos" id="apellidos" placeholder="Ingrese los apellidos" required class="md-input" maxlength="100" />
                    </div>

                    <div class="forma-row">
                        <label for="nickname"><i class="fas fa-user-tag"></i> Nickname (usuario):</label>
                        <input type="text" name="nickname" id="nickname" placeholder="Ingrese el nickname" required class="md-input" maxlength="50" />
                    </div>
                </div>

                <!-- Segunda columna -->
                <div>
                    <div class="forma-row">
                        <label for="correo_electronico"><i class="fas fa-envelope"></i> Correo electrónico:</label>
                        <input type="email" name="correo_electronico" id="correo_electronico" placeholder="Ingrese el correo electrónico" required class="md-input" maxlength="100" />
                    </div>

                    <div class="forma-row">
                        <label for="contrasena"><i class="fas fa-lock"></i> Contraseña:</label>
                        <input type="password" name="contrasena" id="contrasena" placeholder="Ingrese la contraseña" required class="md-input" />
                    </div>

                    <div class="forma-row">
                        <label for="confirmar_contrasena"><i class="fas fa-lock"></i> Confirmar contraseña:</label>
                        <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" placeholder="Confirme la contraseña" required class="md-input" />
                    </div>

                    <div class="forma-row">
                        <label for="rol_id"><i class="fas fa-user-shield"></i> Administrador:</label>
                        <select class="md-input" disabled>
                            <option value="1" selected>Administrador</option>
                        </select>
                        <input type="hidden" name="rol_id" value="1">
                    </div>
                </div>
            </div>

            <div class="buttons-container">
                <button type="submit" class="back_crear">Crear Administrador</button>
                <button type="button" class="back_crear" onclick="window.location.href='../super_menu.html'">Regresar al Menú</button>
            </div>
        </form>
    </div>

    <footer>
        <a>&copy; Todos los derechos reservados al SENA</a>
    </footer>
</body>
<div class="barra-gov">
  <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
</div>
</html>
