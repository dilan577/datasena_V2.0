<?php
$conexion = new mysqli("localhost", "root", "", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$admin = null;
$mensaje = "";
$mensaje_tipo = "";

// Actualización del estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_estado'])) {
    $numero_documento = $_POST['numero_documento'];
    $nuevo_estado = $_POST['nuevo_estado'];

    if (!empty($numero_documento) && !empty($nuevo_estado)) {
        $stmt = $conexion->prepare("UPDATE admin SET estado_habilitacion = ? WHERE numero_documento = ?");
        $stmt->bind_param("ss", $nuevo_estado, $numero_documento);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $mensaje = "✅ Estado actualizado correctamente.";
            $mensaje_tipo = "exito";
        } else {
            $mensaje = "⚠️ No se encontró el administrador o no hubo cambios.";
            $mensaje_tipo = "error";
        }
        $stmt->close();
    }
}

// Búsqueda
if (isset($_GET['numero_documento'])) {
    $numero_documento = $_GET['numero_documento'];
    $stmt = $conexion->prepare("SELECT * FROM admin WHERE numero_documento = ?");
    $stmt->bind_param("s", $numero_documento);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Habilitar/Inhabilitar Administrador</title>
    <link rel="stylesheet" href="habilitar_admin.css">
</head>
<body>
    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
    </div>

    <header>
        <h1>Panel de Habilitación de Administradores</h1>
    </header>

    <main>
        <div class="search-box">
            <form method="get">
                <label for="buscar_doc">Número de Documento:</label>
                <input type="text" id="buscar_doc" name="numero_documento" required>
                <button type="submit">Buscar</button>
            </form>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje <?= $mensaje_tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <?php if ($admin): ?>
            <section class="empresa-detalle">
                <h2>Datos del Administrador</h2>
                <ul>
                    <li><strong>Nombre completo:</strong> <?= htmlspecialchars($admin['nombres'] . ' ' . $admin['apellidos']) ?></li>
                    <li><strong>Documento:</strong> <?= htmlspecialchars($admin['numero_documento']) ?></li>
                    <li><strong>Correo:</strong> <?= htmlspecialchars($admin['correo_electronico']) ?></li>
                    <li><strong>Nickname:</strong> <?= htmlspecialchars($admin['nickname']) ?></li>
                    <li><strong>Estado actual:</strong> <?= $admin['estado_habilitacion'] === 'Activo' ? '✅ Habilitado' : '❌ Inhabilitado' ?></li>
                </ul>

                <form method="post" class="form-estado">
                    <input type="hidden" name="numero_documento" value="<?= htmlspecialchars($admin['numero_documento']) ?>">
                    <label for="nuevo_estado">Cambiar Estado:</label>
                    <select name="nuevo_estado" required>
                        <option value="">Seleccione</option>
                        <option value="Activo">✅ Habilitar</option>
                        <option value="Inactivo">❌ Inhabilitar</option>
                    </select>
                    <div class="botones">
                        <button type="submit" name="actualizar_estado">Actualizar</button>
                        <button type="button" onclick="location.href='../super_menu.html'">Regresar</button>
                    </div>
                </form>
            </section>
        <?php elseif (isset($_GET['numero_documento'])): ?>
            <p class="mensaje error">❌ Administrador no encontrado.</p>
        <?php else: ?>
            <p class="mensaje info">🧭 Ingrese un número de documento para buscar un administrador.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; Todos los derechos reservados al SENA</p>
    </footer>

    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
    </div>
</body>
</html>
