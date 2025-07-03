<?php
$conexion = new mysqli("localhost", "root", "", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$admin = null;
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dato_busqueda'])) {
    $dato = trim($_POST['dato_busqueda']);

    $sql = "SELECT tipo_documento, numero_documento, nombres, apellidos, nickname, correo_electronico, estado_habilitacion 
            FROM admin 
            WHERE numero_documento = ? OR nickname = ?";
    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("Error al preparar la consulta: " . $conexion->error);
    }

    $stmt->bind_param("ss", $dato, $dato);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $admin = $resultado->fetch_assoc();
    } else {
        $mensaje = "⚠️ No se encontró ningún administrador con ese número de documento o nickname.";
    }

    $stmt->close();
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listar Administrador</title>
    <link rel="stylesheet" href="listar_admin_SU_v2.css">
    <link rel="icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
</head>
<body>
<div class="barra-gov">
    <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
</div>

<header>DATASENA</header>
<img src="../../img/logo-sena.png" alt="Logo SENA" class="img">

<div class="form-container">
    <h2>Listar Administrador</h2>

    <?php if (!empty($mensaje)): ?>
        <p class="mensaje-error"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <form action="listar_admin_SU.php" method="post">
        <label for="buscar_dato">Buscar administrador:</label>
        <input type="text" id="buscar_dato" name="dato_busqueda" placeholder="Número de documento o nickname" required>
        <button class="logout-btn" type="submit">🔍 Buscar</button>
    </form>

    <hr>

    <?php if ($admin): ?>
        <div class="empresa-card">
            <p><strong>Tipo de documento:</strong> <?= htmlspecialchars($admin['tipo_documento']) ?></p>
            <p><strong>Número de documento:</strong> <?= htmlspecialchars($admin['numero_documento']) ?></p>
            <p><strong>Nombres:</strong> <?= htmlspecialchars($admin['nombres']) ?></p>
            <p><strong>Apellidos:</strong> <?= htmlspecialchars($admin['apellidos']) ?></p>
            <p><strong>Nickname:</strong> <?= htmlspecialchars($admin['nickname']) ?></p>
            <p><strong>Correo electrónico:</strong> <?= htmlspecialchars($admin['correo_electronico']) ?></p>
            <p><strong>Estado de habilitación:</strong> <?= htmlspecialchars($admin['estado_habilitacion']) ?></p>
        </div>
    <?php endif; ?>

    <div class="back_visual" style="margin-top: 20px;">
        <button class="logout-btn" onclick="window.location.href='../super_menu.html'">⬅ Regresar</button>
    </div>
</div>

<footer>
    <a>&copy; Todos los derechos reservados al SENA</a>
</footer>

<div class="barra-gov">
    <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
</div>
</body>
</html>
