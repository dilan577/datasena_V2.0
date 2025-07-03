<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Inicializar datos del administrador vacíos
$admin = [
    'id' => '',
    'tipo_documento' => '',
    'numero_documento' => '',
    'nombres' => '',
    'apellidos' => '',
    'nickname' => '',
    'correo_electronico' => '',
    'contrasena' => '',
    'rol_id' => ''
];

$mensaje = "";

// Actualizar datos del administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty($_POST['id'])) {
    $id = $_POST['id'];
    $tipo_documento = $_POST['tipo_documento'];
    $numero_documento = $_POST['numero_documento'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $nickname = $_POST['nickname'];
    $correo_electronico = $_POST['correo_electronico'];
    $contrasena = $_POST['contrasena'];
    $rol_id = $_POST['rol_id'];

    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

    $stmt = $conexion->prepare("UPDATE admin SET tipo_documento=?, numero_documento=?, nombres=?, apellidos=?, nickname=?, correo_electronico=?, contrasena=?, rol_id=? WHERE id=?");
    $stmt->bind_param("sisssssii", $tipo_documento, $numero_documento, $nombres, $apellidos, $nickname, $correo_electronico, $contrasena_hash, $rol_id, $id);
    
    if ($stmt->execute()) {
        $mensaje = "Administrador actualizado correctamente.";
    } else {
        $mensaje = "Error al actualizar el administrador.";
    }

    $stmt->close();
}

// Buscar por número de documento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numero_documento']) && empty($_POST['id'])) {
    $numero_documento = $_POST['numero_documento'];

    $stmt = $conexion->prepare("SELECT * FROM admin WHERE numero_documento = ?");
    $stmt->bind_param("i", $numero_documento);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $admin = $resultado->fetch_assoc();
    } else {
        $mensaje = "No se encontró ningún administrador con ese número de documento.";
    }

    $stmt->close();
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visualizar / Actualizar Administrador</title>
    <link rel="stylesheet" href="../../super-administrador/administrador/actualizar_admin_su_v2.css">
    <link rel="icon" href="../img/Logotipo_Datasena.png" type="image/x-icon">
</head>
<body>
    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
    </div>

    <header>DATASENA</header>
    <img src="../../img/logo-sena.png" alt="Logo SENA" class="img">

    <div class="form-container">
        <h2>Visualizar / Actualizar Administrador</h2>

        <?php if ($mensaje): ?>
            <p style="color:green; font-weight:bold;"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <!-- Buscar por número de documento -->
        <form action="actualizar_admin_su.php" method="post">
            <label for="buscar_documento">Buscar por Número de Documento:</label>
            <input type="text" id="buscar_documento" name="numero_documento" placeholder="Ingrese número de documento" required>
            <button class="buscar-form" type="submit">Buscar</button>
        </form>

        <hr>

        <?php if (!empty($admin['id'])): ?>
        <!-- Formulario de edición -->
        <form class="form-grid" action="actualizar_admin_su.php" method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($admin['id']) ?>">

            <div class="form-row">
                <label>Tipo de documento:</label>
                <select name="tipo_documento" required>
                    <option value="CC" <?= $admin['tipo_documento'] == 'CC' ? 'selected' : '' ?>>Cédula de Ciudadanía</option>
                    <option value="TI" <?= $admin['tipo_documento'] == 'TI' ? 'selected' : '' ?>>Tarjeta de Identidad</option>
                    <option value="CE" <?= $admin['tipo_documento'] == 'CE' ? 'selected' : '' ?>>Cédula de Extranjería</option>
                </select>
            </div>

            <div class="form-row">
                <label>Número de documento:</label>
                <input type="text" name="numero_documento" value="<?= htmlspecialchars($admin['numero_documento'] ?? '') ?>" required>
            </div>

            <div class="form-row">
                <label>Nombres:</label>
                <input type="text" name="nombres" value="<?= htmlspecialchars($admin['nombres'] ?? '') ?>" required>
            </div>

            <div class="form-row">
                <label>Apellidos:</label>
                <input type="text" name="apellidos" value="<?= htmlspecialchars($admin['apellidos'] ?? '') ?>" required>
            </div>

            <div class="form-row">
                <label>Nickname:</label>
                <input type="text" name="nickname" value="<?= htmlspecialchars($admin['nickname'] ?? '') ?>" required>
            </div>

            <div class="form-row">
                <label>Correo electrónico:</label>
                <input type="email" name="correo_electronico" value="<?= htmlspecialchars($admin['correo_electronico'] ?? '') ?>" required>
            </div>

            <div class="form-row">
                <label>Contraseña:</label>
                <input type="password" name="contrasena" placeholder="Ingrese nueva contraseña" required>
            </div>

            <div class="form-row">
                <label>ID de Rol:</label>
                <input type="number" name="rol_id" value="<?= htmlspecialchars($admin['rol_id'] ?? '') ?>" required>
            </div>

            <div class="form-row botones-finales">
                <button class="logout-btn" type="submit">Actualizar</button>
                <button class="logout-btn" type="button" onclick="window.location.href='../super_menu.html'">Regresar</button>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <footer>
        <a>&copy; Todos los derechos reservados al SENA</a>
    </footer>

    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
    </div>
</body>
</html>
