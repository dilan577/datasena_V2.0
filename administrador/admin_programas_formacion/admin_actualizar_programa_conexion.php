<?php
$conexion = new mysqli("localhost", "root", "", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$mensaje = "";
$programa = null;

// Si llega un POST con id -> actualizar programa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty($_POST['id'])) {
    $id = $_POST['id'];
    $nombre_programa = $_POST['nombre_programa'];
    $tipo_programa = $_POST['tipo_programa'];
    $numero_ficha = $_POST['numero_ficha'];
    $duracion_programa = $_POST['duracion_programa'];
    $activacion = $_POST['activacion'];

    $stmt = $conexion->prepare("UPDATE programas SET nombre_programa=?, tipo_programa=?, numero_ficha=?, duracion_programa=?, activacion=? WHERE id=?");
    $stmt->bind_param("sssssi", $nombre_programa, $tipo_programa, $numero_ficha, $duracion_programa, $activacion, $id);

    if ($stmt->execute()) {
        $mensaje = "Programa actualizado correctamente.";
    } else {
        $mensaje = "Error al actualizar el programa.";
    }
    $stmt->close();
}

// Si llega un GET con nombre_programa -> buscar programa(s)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['nombre_programa']) && !empty(trim($_GET['nombre_programa']))) {
    $nombre_buscar = "%" . $_GET['nombre_programa'] . "%";

    $stmt = $conexion->prepare("SELECT * FROM programas WHERE nombre_programa LIKE ?");
    $stmt->bind_param("s", $nombre_buscar);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $programa = $resultado->fetch_assoc();
    } else {
        $mensaje = "No se encontró ningún programa con ese nombre.";
    }
    $stmt->close();
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Visualizar / Actualizar Programa</title>
    <link rel="stylesheet" href="../programas_formacion/actualiza_programa.css" />
    <link rel="icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon" />
</head>
<body>
    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo" />
    </div>
    <header>DATASENA</header>
    <img src="../../img/logo-sena.png" alt="Logo SENA" class="img" />

    <div class="form-container">
        <h2>Visualizar / Actualizar Programa</h2>

        <?php if ($mensaje): ?>
            <p style="color:green; font-weight:bold;"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <form method="GET" action="">
            <label for="nombre_programa">Buscar Programa por Nombre:</label>
            <input type="text" name="nombre_programa" id="nombre_programa" value="<?= isset($_GET['nombre_programa']) ? htmlspecialchars($_GET['nombre_programa']) : '' ?>" required />
            <button type="submit" class="buscar-form">Buscar</button>
        </form>

        <hr />

        <?php if (!empty($programa['id'])): ?>
            <form class="form-grid" action="" method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($programa['id']) ?>" />

                <div class="form-row">
                    <label for="nombre_programa">Nombre del Programa:</label>
                    <input type="text" id="nombre_programa" name="nombre_programa" value="<?= htmlspecialchars($programa['nombre_programa']) ?>" required />
                </div>

                <div class="form-row">
                    <label for="tipo_programa">Tipo de Programa:</label>
                    <select id="tipo_programa" name="tipo_programa" required>
                        <option value="Técnico" <?= $programa['tipo_programa'] == 'Técnico' ? 'selected' : '' ?>>Técnico</option>
                        <option value="Tecnólogo" <?= $programa['tipo_programa'] == 'Tecnólogo' ? 'selected' : '' ?>>Tecnólogo</option>
                        <option value="Operario" <?= $programa['tipo_programa'] == 'Operario' ? 'selected' : '' ?>>Operario</option>
                    </select>
                </div>

                <div class="form-row">
                    <label for="numero_ficha">Número de ficha:</label>
                    <input type="text" id="numero_ficha" name="numero_ficha" value="<?= htmlspecialchars($programa['numero_ficha']) ?>" required />
                </div>

                <div class="form-row">
                    <label for="duracion_programa">Duración:</label>
                    <input type="text" id="duracion_programa" name="duracion_programa" value="<?= htmlspecialchars($programa['duracion_programa']) ?>" required />
                </div>

                <div class="form-row">
                    <label for="activacion">Activación:</label>
                    <select id="activacion" name="activacion" required>
                        <option value="activo" <?= $programa['activacion'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= $programa['activacion'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>

                <div class="form-row botones-finales">
                    <button class="logout-btn" type="submit">Actualizar Programa</button>
                    <button class="logout-btn" type="button" onclick="window.location.href='../super_menu.html'">Regresar</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <footer>
        <a>&copy; 2025 Todos los derechos reservados - Proyecto SENA</a>
    </footer>
</body>
<div class="barra-gov">
    <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo" />
</div>
</html>