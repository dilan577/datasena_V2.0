<?php
$conexion = new mysqli("localhost", "root", "", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$programa = null;
$mensaje = "";
$mensaje_tipo = "";

// Actualizar estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_estado'])) {
    $numero_ficha = $_POST['numero_ficha'] ?? '';
    $nuevo_estado = $_POST['nuevo_estado'] ?? '';

    if (!empty($numero_ficha) && !empty($nuevo_estado)) {
        $stmt = $conexion->prepare("UPDATE programas SET activacion = ? WHERE numero_ficha = ?");
        $stmt->bind_param("ss", $nuevo_estado, $numero_ficha);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $mensaje = "✅ Estado actualizado correctamente.";
            $mensaje_tipo = "exito";
        } else {
            $mensaje = "⚠️ No se encontró el programa o no hubo cambios.";
            $mensaje_tipo = "error";
        }
        $stmt->close();
    }
}

// Búsqueda
if (isset($_GET['numero_ficha'])) {
    $numero_ficha = $_GET['numero_ficha'];
    $stmt = $conexion->prepare("SELECT * FROM programas WHERE numero_ficha = ?");
    $stmt->bind_param("s", $numero_ficha);
    $stmt->execute();
    $result = $stmt->get_result();
    $programa = $result->fetch_assoc();
    $stmt->close();
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Habilitar/Inhabilitar Programa</title>
    <link rel="stylesheet" href="../../super-administrador/programas_formacion/habili_inhabilit_programa.css">

</head>
<body>
    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
    </div>

    <header>
        <h1>Panel de Habilitación de Programas</h1>
    </header>

    <main>
        <div class="search-box">
            <form method="get">
                <label for="buscar_ficha">Número de Ficha:</label>
                <input type="text" id="buscar_ficha" name="numero_ficha" required value="<?= htmlspecialchars($_GET['numero_ficha'] ?? '') ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje <?= $mensaje_tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <?php if ($programa): ?>
            <section class="empresa-detalle">
                <h2>Datos del Programa</h2>
                <ul>
                    <li><strong>Nombre del Programa:</strong> <?= htmlspecialchars($programa['nombre_programa']) ?></li>
                    <li><strong>Tipo de Programa:</strong> <?= htmlspecialchars($programa['tipo_programa']) ?></li>
                    <li><strong>Número de Ficha:</strong> <?= htmlspecialchars($programa['numero_ficha']) ?></li>
                    <li><strong>Duración:</strong> <?= htmlspecialchars($programa['duracion_programa']) ?></li>
                    <li><strong>Estado Actual:</strong> <?= $programa['activacion'] === 'activo' ? '✅ Habilitado' : '❌ Inhabilitado' ?></li>
                </ul>

                <form method="post" class="form-estado">
                    <input type="hidden" name="numero_ficha" value="<?= htmlspecialchars($programa['numero_ficha']) ?>">
                    <label for="nuevo_estado">Cambiar Estado:</label>
                    <select name="nuevo_estado" required>
                        <option value="">Seleccione</option>
                        <option value="activo">✅ Habilitar</option>
                        <option value="inactivo">❌ Inhabilitar</option>
                    </select>
                    <div class="botones">
                        <button type="submit" name="actualizar_estado">Actualizar</button>
                        <button type="button" onclick="location.href='../super_menu.html'">Regresar</button>
                    </div>
                </form>
            </section>
        <?php elseif (isset($_GET['numero_ficha'])): ?>
            <p class="mensaje error">❌ Programa no encontrado.</p>
        <?php else: ?>
            <p class="mensaje info">🧭 Ingrese un número de ficha para buscar un programa.</p>
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
