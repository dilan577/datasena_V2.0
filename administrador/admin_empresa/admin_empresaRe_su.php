<?php
// ================= VALIDACIÓN DE SESIÓN =================
session_start();

// Validar que esté logueado y que sea superadministrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../inicio_sesion.html");
    exit();
}   
// ========================================================

$errores = [];
$datos = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Lista de campos obligatorios
    $campos = [
        'tipo_documento', 'numero_identidad', 'nickname',
        'telefono', 'correo', 'direccion',
        'actividad_economica', 'estado'
    ];

    // Recorremos los campos para validar que no vengan vacíos
    foreach ($campos as $campo) {
        $datos[$campo] = trim($_POST[$campo] ?? '');
        if ($datos[$campo] === '' || $datos[$campo] === null) {
            $errores[$campo] = "Este campo es obligatorio.";
        }
    }

    // Capturamos las contraseñas aparte
    $datos['contrasena'] = trim($_POST['contrasena'] ?? '');
    $datos['confirmar_contrasena'] = trim($_POST['confirmar_contrasena'] ?? '');

    // Validación de correo electrónico
    if (!empty($datos['correo']) && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
        $errores['correo'] = "Correo electrónico no válido.";
    }

    // Validación de teléfono (exactamente 10 dígitos)
    if (!empty($datos['telefono']) && !preg_match('/^\d{10}$/', $datos['telefono'])) {
        $errores['telefono'] = "El teléfono debe tener exactamente 10 dígitos.";
    }

    // Validación de número de documento (8 a 12 dígitos)
    if (!empty($datos['numero_identidad']) && !preg_match('/^\d{8,12}$/', $datos['numero_identidad'])) {
        $errores['numero_identidad'] = "El número de identidad debe tener entre 8 y 12 dígitos numéricos.";
    }

    // Validación de nickname (letras, números y espacios permitidos)
    if (!empty($datos['nickname']) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 ]+$/u', $datos['nickname'])) {
        $errores['nickname'] = "El nombre de la empresa solo puede contener letras, números y espacios.";
    }

    // Validación de contraseña segura
    if (empty($datos['contrasena'])) {
        $errores['contrasena'] = "La contraseña es obligatoria.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#+_\-])[A-Za-z\d@$!%*?&#+_\-]{8,}$/', $datos['contrasena'])) {
        $errores['contrasena'] = "La contraseña debe tener al menos 8 caracteres, incluyendo una mayúscula, una minúscula, un número y un carácter especial.";
    }

    // Confirmación de contraseña
    if (empty($datos['confirmar_contrasena'])) {
        $errores['confirmar_contrasena'] = "Por favor, confirme su contraseña.";
    } elseif ($datos['contrasena'] !== $datos['confirmar_contrasena']) {
        $errores['confirmar_contrasena'] = "Las contraseñas no coinciden.";
    }

    // Si no hay errores hasta ahora, procedemos a validar unicidad en la base de datos
    if (empty($errores)) {
        try {
            // Conexión con la base de datos usando PDO
            $conexion = new PDO("mysql:host=localhost;dbname=datasena_db;charset=utf8", "root", "");
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 🔎 Verificación de que el número de identidad no esté repetido
            $stmt = $conexion->prepare("SELECT id FROM empresas WHERE numero_identidad = :numero_identidad LIMIT 1");
            $stmt->execute([':numero_identidad' => $datos['numero_identidad']]);
            if ($stmt->fetch()) {
                $errores['numero_identidad'] = "El número de documento ya está registrado.";
            }

            // 🔎 Verificación de que el correo no esté repetido
            $stmt = $conexion->prepare("SELECT id FROM empresas WHERE correo = :correo LIMIT 1");
            $stmt->execute([':correo' => $datos['correo']]);
            if ($stmt->fetch()) {
                $errores['correo'] = "El correo ya está registrado.";
            }

            // 🔎 Verificación de que el teléfono no esté repetido
            $stmt = $conexion->prepare("SELECT id FROM empresas WHERE telefono = :telefono LIMIT 1");
            $stmt->execute([':telefono' => $datos['telefono']]);
            if ($stmt->fetch()) {
                $errores['telefono'] = "El teléfono ya está registrado.";
            }

            // Si después de verificar unicidad no hay errores, insertamos
            if (empty($errores)) {
                // Hasheamos la contraseña antes de guardar
                $contrasenaHash = password_hash($datos['contrasena'], PASSWORD_DEFAULT);

                // Query de inserción
                $sql = "INSERT INTO empresas (
                            tipo_documento, numero_identidad, nickname, telefono,
                            correo, direccion, actividad_economica, estado, contrasena
                        ) VALUES (
                            :tipo_documento, :numero_identidad, :nickname, :telefono,
                            :correo, :direccion, :actividad_economica, :estado, :contrasena
                        )";

                $stmt = $conexion->prepare($sql);

                // Asignamos los valores a la query
                foreach ($campos as $campo) {
                    $stmt->bindValue(":$campo", $datos[$campo]);
                }
                $stmt->bindValue(':contrasena', $contrasenaHash);

                // Ejecutamos el INSERT
                $stmt->execute();

                // Mensaje de éxito
                $exito = "Empresa registrada exitosamente.";
                $datos = [];
            }

        } catch (PDOException $e) {
            $errores['general'] = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Empresa</title>
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../administrador/admin_empresa/admin_empresaRe_su.css">
</head>
<body>
<!--barra del gov superior-->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/ " target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>
<h1>DATASENA</h1>
<img src="../../img/logo-sena.png" alt="Logo SENA" class="img" />
<div class="forma-container">
    <h3>Registro de Empresa</h3>
    <?php if (!empty($errores['general'])): ?>
        <div class="mensaje-error">❌ <?= htmlspecialchars($errores['general']) ?></div>
    <?php endif; ?>
    <?php if (!empty($exito)): ?>
        <div class="mensaje-exito">✅ <?= htmlspecialchars($exito) ?></div>
    <?php endif; ?>
    <form action="" method="POST">
        <div class="forma-grid">
            <!-- Primera columna -->
            <div>
                <div class="forma-row">
                    <label for="tipo_documento">📄 Tipo de Documento:</label>
                    <select id="tipo_documento" name="tipo_documento" required>
                        <option value="">Seleccione una opción</option>
                        <option value="NIT" <?= ($datos['tipo_documento'] ?? '') === 'NIT' ? 'selected' : '' ?>>NIT</option>
                        <option value="Registro Mercantil" <?= ($datos['tipo_documento'] ?? '') === 'Registro Mercantil' ? 'selected' : '' ?>>Registro Mercantil</option>
                        <option value="Registro Cámara de Comercio Extranjera" <?= ($datos['tipo_documento'] ?? '') === 'Registro Cámara de Comercio Extranjera' ? 'selected' : '' ?>>Registro Cámara de Comercio Extranjera</option>
                        <option value="Pasaporte Empresarial" <?= ($datos['tipo_documento'] ?? '') === 'Pasaporte Empresarial' ? 'selected' : '' ?>>Pasaporte Empresarial</option>
                        <option value="RUT" <?= ($datos['tipo_documento'] ?? '') === 'RUT' ? 'selected' : '' ?>>RUT</option>
                        <option value="Licencia Municipal" <?= ($datos['tipo_documento'] ?? '') === 'Licencia Municipal' ? 'selected' : '' ?>>Licencia Municipal</option>
                    </select>
                    <?php if (!empty($errores['tipo_documento'])): ?>
                        <div class="mensaje-error">❌ <?= htmlspecialchars($errores['tipo_documento']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="forma-row">
                    <label for="numero_identidad">🔢 Número de Documento:</label>
                    <input type="text" id="numero_identidad" name="numero_identidad"
                        pattern="\d{8,12}" title="Debe tener entre 8 y 12 dígitos numéricos"
                        value="<?= htmlspecialchars($datos['numero_identidad'] ?? '') ?>" required>
                    <?php if (!empty($errores['numero_identidad'])): ?>
                        <div class="mensaje-error">❌ <?= htmlspecialchars($errores['numero_identidad']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="forma-row">
                    <label for="nickname">🏢 Nombre de la Empresa:</label>
                    <input type="text" id="nickname" name="nickname" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 ]+" title="Solo letras, números y espacios" value="<?= htmlspecialchars($datos['nickname'] ?? '') ?>" required>
                    <?php if (!empty($errores['nickname'])): ?>
                        <div class="mensaje-error">❌ <?= htmlspecialchars($errores['nickname']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="forma-row">
                    <label for="telefono">📞 Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" pattern="\d{10}" title="Debe tener 10 dígitos" value="<?= htmlspecialchars($datos['telefono'] ?? '') ?>" required>
                    <?php if (!empty($errores['telefono'])): ?>
                        <div class="mensaje-error">❌ <?= htmlspecialchars($errores['telefono']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="forma-row">
                    <label for="correo">✉️ Correo Electrónico:</label>
                    <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($datos['correo'] ?? '') ?>" required>
                    <?php if (!empty($errores['correo'])): ?>
                        <div class="mensaje-error">❌ <?= htmlspecialchars($errores['correo']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="forma-row">
                    <label for="direccion">📍 Dirección:</label>
                    <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($datos['direccion'] ?? '') ?>" required>
                    <?php if (!empty($errores['direccion'])): ?>
                        <div class="mensaje-error">❌ <?= htmlspecialchars($errores['direccion']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Segunda columna -->
            <div>
                <div class="forma-row">
                    <label for="actividad_economica">💼 Actividad Económica:</label>
                    <input type="text" id="actividad_economica" name="actividad_economica" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 ,.]+" title="Solo letras, números, comas y puntos" value="<?= htmlspecialchars($datos['actividad_economica'] ?? '') ?>" required>
                    <?php if (!empty($errores['actividad_economica'])): ?>
                        <div class="mensaje-error">❌ <?= htmlspecialchars($errores['actividad_economica']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="forma-row">
                    <label for="estado">⚙️ Estado:</label>
                    <select id="estado" name="estado" required>
                        <option value="">Seleccione...</option>
                        <option value="1" <?= ($datos['estado'] ?? '') == '1' ? 'selected' : '' ?>>Activo</option>
                        <option value="0" <?= ($datos['estado'] ?? '') == '0' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                    <?php if (!empty($errores['estado'])): ?>
                        <div class="mensaje-error">❌ <?= htmlspecialchars($errores['estado']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="forma-row">
                    <label for="contrasena">🔒 Contraseña:</label>
                    <input type="password" id="contrasena" name="contrasena" required>
                    <?php if (!empty($errores['contrasena'])): ?>
                        <div class="mensaje-error">❌ <?= htmlspecialchars($errores['contrasena']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="forma-row">
                    <label for="confirmar_contrasena">🔁 Confirmar Contraseña:</label>
                    <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
                    <?php if (!empty($errores['confirmar_contrasena'])): ?>
                        <div class="mensaje-error">❌ <?= htmlspecialchars($errores['confirmar_contrasena']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="logout-buttons-container">
            <button type="submit" class="logout-btn">✅ Crear</button>
            <button type="button" class="logout-btn" onclick="window.location.href='../admin_menu.html'">↩️ Regresar</button>
        </div>
    </form>
</div>
<footer>&copy; 2025 Todos los derechos reservados - Proyecto SENA</footer>
<!--barra del gov inferior-->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/ " target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>
</body>
</html>