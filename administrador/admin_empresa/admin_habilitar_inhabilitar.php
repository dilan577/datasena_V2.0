<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "datasenn_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Inicialización
$empresas = null;
$mensaje = "";
$mensaje_tipo = "";

// Actualización del estado de habilitación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_estado'])) {
    $cc = $_POST['cc'] ?? '';
    $nuevo_estado = $_POST['nuevo_estado'] ?? '';

    if (!empty($cc) && !empty($nuevo_estado)) {
        $stmt = $conexion->prepare("UPDATE empresas SET estado_habilitacion = ? WHERE numero_identidad = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $nuevo_estado, $cc);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $mensaje = "✅ Estado actualizado correctamente.";
                $mensaje_tipo = "exito";
            } else {
                $mensaje = "⚠️ No se encontró la empresa o no hubo cambios.";
                $mensaje_tipo = "error";
            }
            $stmt->close();
        } else {
            $mensaje = "❌ Error: " . $conexion->error;
            $mensaje_tipo = "error";
        }
    }
}

// Búsqueda por número de documento
if (isset($_GET['cc'])) {
    $cc = $_GET['cc'];
    $stmt = $conexion->prepare("SELECT * FROM empresas WHERE numero_identidad = ?");
    if ($stmt) {
        $stmt->bind_param("s", $cc);
        $stmt->execute();
        $result = $stmt->get_result();
        $empresas = $result->fetch_assoc();
        $stmt->close();
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habilitar/Inhabilitar Empresa</title>
    <link rel="stylesheet" href="../../super-administrador//empresa/habilitar_inhabilitar.css">
</head>
<body>
    <header>
        <h1>Panel de Habilitación de Empresas</h1>
    </header>
    <main>
<!-- Contenedor centrado para búsqueda -->
<div class="search-box-wrapper">
    <div class="search-box">
        <h4>🔍 Buscar Empresa</h4>
        <form method="get" class="search-form">
            <label for="buscar_cc">Número de Documento:</label>
            <input type="text" id="buscar_cc" name="cc" required class="md-input">
            <button type="submit" class="search-btn">Buscar</button>
        </form>
    </div>
</div>


        <?php if (!empty($mensaje)) : ?>
            <div class="mensaje <?= $mensaje_tipo ?>"> <?= htmlspecialchars($mensaje) ?> </div>
        <?php endif; ?>

        <?php if ($empresas): ?>
        <section class="empresa-detalle">
            <h3>Detalles de la Empresa</h3>
            <ul class="datos-empresa">
                <li><strong>Tipo Documento:</strong> <?= htmlspecialchars($empresas['tipo_documento']) ?></li>
                <li><strong>Documento:</strong> <?= htmlspecialchars($empresas['numero_identidad']) ?></li>
                <li><strong>Empresa:</strong> <?= htmlspecialchars($empresas['nickname']) ?></li>
                <li><strong>Teléfono:</strong> <?= htmlspecialchars($empresas['telefono']) ?></li>
                <li><strong>Correo:</strong> <?= htmlspecialchars($empresas['correo']) ?></li>
                <li><strong>Dirección:</strong> <?= htmlspecialchars($empresas['direccion']) ?></li>
                <li><strong>Actividad:</strong> <?= htmlspecialchars($empresas['actividad_economica']) ?></li>
                <li><strong>Estado Actual:</strong> <?= $empresas['estado_habilitacion'] === 'Activo' ? '✅ Habilitado' : '❌ Inhabilitado' ?></li>
            </ul>

            <form method="post" class="form-estado">
                <input type="hidden" name="cc" value="<?= htmlspecialchars($empresas['numero_identidad']) ?>">
                <label for="nuevo_estado">Cambiar Estado:</label>
                <select name="nuevo_estado" required>
                    <option value="">Seleccione</option>
                    <option value="Activo">✅ Habilitar</option>
                    <option value="Inactivo">❌ Inhabilitar</option>
                </select>
                <div class="btn-group">
                    <button type="submit" name="actualizar_estado" class="btn actualizar">Actualizar</button>
                    <button type="button" class="btn regresar" onclick="location.href='../super_menu.html'">Regresar</button>
                </div>
            </form>
        </section>
        <?php elseif (isset($_GET['cc'])): ?>
            <p class="mensaje error">❌ Empresa no encontrada. Verifique e intente nuevamente.</p>
        <?php else: ?>
            <p class="mensaje info">🧭 Ingrese un documento para iniciar la búsqueda.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; Todos los derechos reservados al SENA</p>
    </footer>
</body>
</html>
