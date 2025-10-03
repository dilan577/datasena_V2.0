<?php
// Inicializamos arrays para almacenar errores y los datos enviados por el formulario.
$errores = [];
$datos = [];

// Verificamos que la solicitud sea de tipo POST (es decir, que se haya enviado el formulario).
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Definimos los campos obligatorios del formulario (excepto contraseña y confirmación).
    $campos = [
        'tipo_documento', 'numero_identidad', 'nickname',
        'telefono', 'correo', 'direccion',
        'actividad_economica', 'estado'
    ];

    // Recorremos cada campo para validar que no esté vacío.
    foreach ($campos as $campo) {
        // Obtenemos el valor del campo, eliminamos espacios innecesarios y lo guardamos en $datos.
        $datos[$campo] = trim($_POST[$campo] ?? '');
        // Si el campo está vacío o es null, agregamos un mensaje de error.
        if ($datos[$campo] === '' || $datos[$campo] === null) {
            $errores[$campo] = "Este campo es obligatorio.";
        }
    }

    // Procesamos los campos de contraseña por separado.
    $datos['contrasena'] = trim($_POST['contrasena'] ?? '');
    $datos['confirmar_contrasena'] = trim($_POST['confirmar_contrasena'] ?? '');

    // Validación del correo: debe tener un formato válido si no está vacío.
    if (!empty($datos['correo']) && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
        $errores['correo'] = "Correo electrónico no válido.";
    }

    // Validación del teléfono: debe tener exactamente 10 dígitos numéricos.
    if (!empty($datos['telefono']) && !preg_match('/^\d{10}$/', $datos['telefono'])) {
        $errores['telefono'] = "El teléfono debe tener exactamente 10 dígitos.";
    }

    // Validación del número de identidad: entre 8 y 12 dígitos numéricos.
    if (!empty($datos['numero_identidad']) && !preg_match('/^\d{8,12}$/', $datos['numero_identidad'])) {
        $errores['numero_identidad'] = "El número de identidad debe tener entre 8 y 12 dígitos numéricos.";
    }

    // Validación del nickname (nombre de la empresa): solo letras, números, espacios y tildes (UTF-8).
    if (!empty($datos['nickname']) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 ]+$/u', $datos['nickname'])) {
        $errores['nickname'] = "El nombre de la empresa solo puede contener letras, números y espacios.";
    }

    // Validación de la contraseña:
    // - Obligatoria.
    // - Debe cumplir con: 8+ caracteres, al menos una mayúscula, una minúscula, un número y un carácter especial.
    if (empty($datos['contrasena'])) {
        $errores['contrasena'] = "La contraseña es obligatoria.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#+\-_])[A-Za-z\d@$!%*?&#+\-_]{8,}$/', $datos['contrasena'])) {
        $errores['contrasena'] = "Debe tener 8+ caracteres, una mayúscula, una minúscula, un número y un carácter especial.";
    }

    // Validación de la confirmación de la contraseña:
    // - Obligatoria.
    // - Debe coincidir exactamente con la contraseña original.
    if (empty($datos['confirmar_contrasena'])) {
        $errores['confirmar_contrasena'] = "Por favor, confirme su contraseña.";
    } elseif ($datos['contrasena'] !== $datos['confirmar_contrasena']) {
        $errores['confirmar_contrasena'] = "Las contraseñas no coinciden.";
    }

    // Si no hay errores hasta ahora, procedemos a verificar unicidad en la base de datos.
    if (empty($errores)) {
        try {
            // Conexión a la base de datos usando PDO (con manejo de excepciones).
            $conexion = new PDO("mysql:host=localhost;dbname=datasena_db;charset=utf8", "root", "");
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Verificamos que el número de identidad no esté ya registrado.
            $stmt = $conexion->prepare("SELECT id FROM empresas WHERE numero_identidad = :numero_identidad LIMIT 1");
            $stmt->execute([':numero_identidad' => $datos['numero_identidad']]);
            if ($stmt->fetch()) {
                $errores['numero_identidad'] = "El número de documento ya está registrado.";
            }

            // Verificamos que el correo no esté ya registrado.
            $stmt = $conexion->prepare("SELECT id FROM empresas WHERE correo = :correo LIMIT 1");
            $stmt->execute([':correo' => $datos['correo']]);
            if ($stmt->fetch()) {
                $errores['correo'] = "El correo ya está registrado.";
            }

            // Verificamos que el teléfono no esté ya registrado.
            $stmt = $conexion->prepare("SELECT id FROM empresas WHERE telefono = :telefono LIMIT 1");
            $stmt->execute([':telefono' => $datos['telefono']]);
            if ($stmt->fetch()) {
                $errores['telefono'] = "El teléfono ya está registrado.";
            }

            // Si después de las verificaciones en BD aún no hay errores, insertamos el registro.
            if (empty($errores)) {
                // Hasheamos la contraseña para almacenarla de forma segura.
                $contrasenaHash = password_hash($datos['contrasena'], PASSWORD_DEFAULT);

                // Sentencia SQL para insertar la nueva empresa.
                $sql = "INSERT INTO empresas (
                            tipo_documento, numero_identidad, nickname, telefono,
                            correo, direccion, actividad_economica, estado, contrasena
                        ) VALUES (
                            :tipo_documento, :numero_identidad, :nickname, :telefono,
                            :correo, :direccion, :actividad_economica, :estado, :contrasena
                        )";

                $stmt = $conexion->prepare($sql);

                // Vinculamos los valores de los campos obligatorios.
                foreach ($campos as $campo) {
                    $stmt->bindValue(":$campo", $datos[$campo]);
                }
                // Vinculamos la contraseña hasheada.
                $stmt->bindValue(':contrasena', $contrasenaHash);
                $stmt->execute();

                // Mensaje de éxito y limpiamos los datos del formulario.
                $exito = "Empresa registrada exitosamente.";
                $datos = [];
            }

        } catch (PDOException $e) {
            // En caso de error de base de datos, mostramos un mensaje genérico (sin exponer detalles técnicos).
            $errores['general'] = "Error en la base de datos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Empresa</title>
    <!-- Favicon personalizado -->
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
    <!-- Soporte para dispositivos móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Enlace al archivo CSS personalizado -->
    <link rel="stylesheet" href="../../super-administrador/empresa/empresaRe_su.css">
</head>
<body>
<!-- Barra superior del gobierno colombiano (GOV.CO) -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<!-- Título principal del sistema -->
<h1>DATASENA</h1>
<!-- Logo del SENA -->
<img src="../../img/logo-sena.png" alt="Logo SENA" class="img" />

<!-- Contenedor principal del formulario -->
<div class="forma-container">
    <h3>Registro de Empresa</h3>

    <!-- Mostrar mensaje de error general (por ejemplo, fallo en la base de datos) -->
    <?php if (!empty($errores['general'])): ?>
        <div class="mensaje-error">❌ <?= htmlspecialchars($errores['general']) ?></div>
    <?php endif; ?>

    <!-- Mostrar mensaje de éxito si el registro fue correcto -->
    <?php if (!empty($exito)): ?>
        <div class="mensaje-exito">✅ <?= htmlspecialchars($exito) ?></div>
    <?php endif; ?>

    <!-- Formulario de registro (envía a la misma página) -->
    <form action="" method="POST">
        <div class="forma-grid">
            <!-- Primera columna del formulario -->
            <div>
                <!-- Tipo de documento: desplegable con opciones predefinidas -->
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
                    <!-- Mostrar error si aplica -->
                    <?php if (!empty($errores['tipo_documento'])): ?>
                        <small class="error-text"><?= htmlspecialchars($errores['tipo_documento']) ?></small>
                    <?php endif; ?>
                </div>

                <!-- Número de identidad -->
                <div class="forma-row">
                    <label for="numero_identidad">🔢 Número de Documento:</label>
                    <input type="text" id="numero_identidad" name="numero_identidad"
                           value="<?= htmlspecialchars($datos['numero_identidad'] ?? '') ?>" required>
                    <?php if (!empty($errores['numero_identidad'])): ?>
                        <small class="error-text"><?= htmlspecialchars($errores['numero_identidad']) ?></small>
                    <?php endif; ?>
                </div>

                <!-- Nombre de la empresa (nickname) -->
                <div class="forma-row">
                    <label for="nickname">🏢 Nombre de la Empresa:</label>
                    <input type="text" id="nickname" name="nickname"
                           value="<?= htmlspecialchars($datos['nickname'] ?? '') ?>" required>
                    <?php if (!empty($errores['nickname'])): ?>
                        <small class="error-text"><?= htmlspecialchars($errores['nickname']) ?></small>
                    <?php endif; ?>
                </div>

                <!-- Teléfono -->
                <div class="forma-row">
                    <label for="telefono">📞 Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono"
                           value="<?= htmlspecialchars($datos['telefono'] ?? '') ?>" required>
                    <?php if (!empty($errores['telefono'])): ?>
                        <small class="error-text"><?= htmlspecialchars($errores['telefono']) ?></small>
                    <?php endif; ?>
                </div>

                <!-- Correo electrónico -->
                <div class="forma-row">
                    <label for="correo">✉️ Correo Electrónico:</label>
                    <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($datos['correo'] ?? '') ?>" required>
                    <?php if (!empty($errores['correo'])): ?>
                        <small class="error-text"><?= htmlspecialchars($errores['correo']) ?></small>
                    <?php endif; ?>
                </div>

                <!-- Dirección -->
                <div class="forma-row">
                    <label for="direccion">📍 Dirección:</label>
                    <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($datos['direccion'] ?? '') ?>" required>
                    <?php if (!empty($errores['direccion'])): ?>
                        <small class="error-text"><?= htmlspecialchars($errores['direccion']) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Segunda columna del formulario -->
            <div>
                <!-- Actividad económica -->
                <div class="forma-row">
                    <label for="actividad_economica">💼 Actividad Económica:</label>
                    <input type="text" id="actividad_economica" name="actividad_economica"
                           value="<?= htmlspecialchars($datos['actividad_economica'] ?? '') ?>" required>
                    <?php if (!empty($errores['actividad_economica'])): ?>
                        <small class="error-text"><?= htmlspecialchars($errores['actividad_economica']) ?></small>
                    <?php endif; ?>
                </div>

                <!-- Estado (activo/inactivo) -->
                <div class="forma-row">
                    <label for="estado">⚙️ Estado:</label>
                    <select id="estado" name="estado" required>
                        <option value="">Seleccione...</option>
                        <option value="1" <?= ($datos['estado'] ?? '') == '1' ? 'selected' : '' ?>>Activo</option>
                        <option value="0" <?= ($datos['estado'] ?? '') == '0' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                    <?php if (!empty($errores['estado'])): ?>
                        <small class="error-text"><?= htmlspecialchars($errores['estado']) ?></small>
                    <?php endif; ?>
                </div>

                <!-- Contraseña -->
                <div class="forma-row">
                    <label for="contrasena">🔒 Contraseña:</label>
                    <input type="password" id="contrasena" name="contrasena" required>
                    <?php if (!empty($errores['contrasena'])): ?>
                        <small class="error-text"><?= htmlspecialchars($errores['contrasena']) ?></small>
                    <?php endif; ?>
                </div>

                <!-- Confirmación de contraseña -->
                <div class="forma-row">
                    <label for="confirmar_contrasena">🔁 Confirmar Contraseña:</label>
                    <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
                    <?php if (!empty($errores['confirmar_contrasena'])): ?>
                        <small class="error-text"><?= htmlspecialchars($errores['confirmar_contrasena']) ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Botones de acción: registrar o regresar -->
        <div class="logout-buttons-container">
            <button type="submit" class="logout-btn">✅ Registrar Empresa</button>
            <button type="button" class="logout-btn" onclick="window.location.href='../super_menu.html'">↩️ Regresar</button>
        </div>
    </form>
</div>

<!-- Pie de página -->
<footer>&copy; 2025 Todos los derechos reservados - Proyecto SENA</footer>

<!-- Barra inferior del gobierno (repetida por diseño) -->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>
</body>
</html>