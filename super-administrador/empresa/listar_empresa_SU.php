<?php
$conexion = new mysqli("localhost", "root", "", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$empresa = null;
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dato_busqueda'])) {
    $dato = trim($_POST['dato_busqueda']);

    $sql = "SELECT * FROM empresas WHERE numero_identidad = ? OR nickname = ?";
    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("Error al preparar la consulta: " . $conexion->error);
    }

    $stmt->bind_param("ss", $dato, $dato);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $empresa = $resultado->fetch_assoc();
    } else {
        $mensaje = "⚠️ No se encontró empresa con ese número de identidad o nickname.";
    }

    $stmt->close();
}
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listar Empresa</title>
    <link rel="stylesheet" href="listar_empresa_su.css">
    <link rel="icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
</head>
<body>
    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
    </div>

    <header>DATASENA</header>
    <img src="../../img/logo-sena.png" alt="Logo SENA" class="img">

    <div class="form-container">
        <h2>Listar Empresa</h2>

        <?php if (!empty($mensaje)): ?>
            <p class="mensaje-error"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <form action="listar_empresa_su.php" method="post">
            <label for="buscar_dato">Buscar empresa:</label>
            <input type="text" id="buscar_dato" name="dato_busqueda" placeholder="Número de identidad o nickname" required>
            <button class="logout-btn" type="submit">🔍 Buscar</button>
        </form>

        <hr>

        <?php if ($empresa): ?>
            <div class="empresa-card">
                <p><strong>Tipo de documento:</strong> <?= htmlspecialchars($empresa['tipo_documento']) ?></p>
                <p><strong>Número de identidad:</strong> <?= htmlspecialchars($empresa['numero_identidad']) ?></p>
                <p><strong>Nombre:</strong> <?= htmlspecialchars($empresa['nickname']) ?></p>
                <p><strong>Teléfono:</strong> <?= htmlspecialchars($empresa['telefono']) ?></p>
                <p><strong>Correo:</strong> <?= htmlspecialchars($empresa['correo']) ?></p>
                <p><strong>Dirección:</strong> <?= htmlspecialchars($empresa['direccion']) ?></p>
                <p><strong>Actividad Económica:</strong> <?= htmlspecialchars($empresa['actividad_economica']) ?></p>
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
