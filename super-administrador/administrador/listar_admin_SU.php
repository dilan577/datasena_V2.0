<?php
$conexion = new mysqli("localhost", "root", " ", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$admin = null;
$todos_admins = [];
$mensaje = "";

// Buscar uno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dato_busqueda']) && !isset($_POST['buscar_todos'])) {
    $dato = trim($_POST['dato_busqueda']);

    $sql = "SELECT tipo_documento, numero_documento, nombres, apellidos, nickname, correo_electronico, estado_habilitacion, fecha_creacion 
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

// Buscar todos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_todos'])) {
    $sql = "SELECT tipo_documento, numero_documento, nombres, apellidos, nickname, correo_electronico, estado_habilitacion, fecha_creacion FROM admin";
    $resultado = $conexion->query($sql);
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $todos_admins[] = $fila;
        }
    } else {
        $mensaje = "❌ No hay administradores registrados.";
    }
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listar Administrador</title>
    <link rel="stylesheet" href="listar_admin_SU_v2.css">
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
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

    <form action="listar_admin_SU.php" method="post" style="display:flex; gap:10px; flex-wrap: wrap; align-items: center;">
        <label for="buscar_dato">Buscar administrador:</label>
        <input type="text" id="buscar_dato" name="dato_busqueda" placeholder="Número de documento o nickname" required>
        <button class="logout-btn" type="submit">🔍 Buscar</button>
        <button class="logout-btn" type="submit" name="buscar_todos" onclick="document.getElementById('buscar_dato').removeAttribute('required')">📋 Mostrar Todos</button>
        <button type="button" class="logout-btn" onclick="window.location.href='../super_menu.html'">↩️ Regresar</button>

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
            <p><strong>Fecha de creación:</strong> <?= htmlspecialchars($admin['fecha_creacion'] ?? 'Sin fecha') ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($todos_admins)): ?>
        <h3>📋 Administradores Registrados</h3>
        <div style="overflow-x:auto;">
            <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; background:#fff;">
                <thead style="background-color:#0078c0; color:white;">
                    <tr>
                        <th>Tipo Doc</th>
                        <th>Número</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Nickname</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todos_admins as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['tipo_documento']) ?></td>
                            <td><?= htmlspecialchars($a['numero_documento']) ?></td>
                            <td><?= htmlspecialchars($a['nombres']) ?></td>
                            <td><?= htmlspecialchars($a['apellidos']) ?></td>
                            <td><?= htmlspecialchars($a['nickname']) ?></td>
                            <td><?= htmlspecialchars($a['correo_electronico']) ?></td>
                            <td><?= isset($a['estado_habilitacion']) ? htmlspecialchars($a['estado_habilitacion']) : 'N/D' ?></td>
                            <td><?= isset($a['fecha_creacion']) ? htmlspecialchars($a['fecha_creacion']) : 'Sin fecha' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
