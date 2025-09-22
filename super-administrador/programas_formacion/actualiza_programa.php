<?php
$conexion = new mysqli("localhost", "root", "", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$mensaje = "";
$programa = null;
$programas = []; 
$mostrarTituloLista = false;

// POST: Actualizar programa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty($_POST['id'])) {
    $id = $_POST['id'];
    $nombre_programa = trim($_POST['nombre_programa']);
    $tipo_programa = trim($_POST['tipo_programa']);
    $numero_ficha = trim($_POST['numero_ficha']);
    $duracion_programa = trim($_POST['duracion_programa']);
    $activacion = trim($_POST['activacion']);

    // Validaciones
    if (empty($nombre_programa) || empty($tipo_programa) || empty($numero_ficha) || empty($duracion_programa) || empty($activacion)) {
        $mensaje = "Todos los campos son obligatorios.";
    } elseif (!is_numeric($numero_ficha) || $numero_ficha < 0) {
        $mensaje = "El número de ficha debe ser un número positivo.";
    } elseif (!is_numeric($duracion_programa) || $duracion_programa < 0) {
        $mensaje = "La duración del programa debe ser un número positivo.";
    } else {
        // Validar duplicados por nombre
        $stmtCheckNombre = $conexion->prepare("SELECT id FROM programas WHERE nombre_programa = ? AND id != ?");
        $stmtCheckNombre->bind_param("si", $nombre_programa, $id);
        $stmtCheckNombre->execute();
        $resCheckNombre = $stmtCheckNombre->get_result();
        if ($resCheckNombre->num_rows > 0) {
            $mensaje = "Ya existe otro programa con ese nombre.";
        }
        $stmtCheckNombre->close();

        // Validar duplicados por número de ficha
        $stmtCheckFicha = $conexion->prepare("SELECT id FROM programas WHERE numero_ficha = ? AND id != ?");
        $stmtCheckFicha->bind_param("si", $numero_ficha, $id);
        $stmtCheckFicha->execute();
        $resCheckFicha = $stmtCheckFicha->get_result();
        if ($resCheckFicha->num_rows > 0) {
            $mensaje = "Ya existe otro programa con ese número de ficha.";
        }
        $stmtCheckFicha->close();

        // Ejecutar UPDATE si no hay errores
        if ($mensaje === "") {
            $stmt = $conexion->prepare("UPDATE programas SET nombre_programa=?, tipo_programa=?, numero_ficha=?, duracion_programa=?, activacion=? WHERE id=?");
            $stmt->bind_param("sssssi", $nombre_programa, $tipo_programa, $numero_ficha, $duracion_programa, $activacion, $id);
            if ($stmt->execute()) {
                $mensaje = "Programa actualizado correctamente.";
            } else {
                $mensaje = "Error al actualizar el programa.";
            }
            $stmt->close();
        }
    }
}

// GET: búsqueda o mostrar todos
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['buscar'])) {
    $nombre_programa_input = trim($_GET['nombre_programa']);
    if (empty($nombre_programa_input)) {
        $mensaje = "Por favor ingrese un nombre para buscar.";
    } else {
        $nombre_buscar = "%" . $nombre_programa_input . "%";
        $stmt = $conexion->prepare("SELECT * FROM programas WHERE nombre_programa LIKE ?");
        $stmt->bind_param("s", $nombre_buscar);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $programas = $resultado->fetch_all(MYSQLI_ASSOC);

        if (count($programas) === 1) {
            $programa = $programas[0];
            $programas = []; 
        } else {
            $programa = null;
            $mostrarTituloLista = true;
        }
        $stmt->close();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['mostrar_todos'])) {
    $resultado = $conexion->query("SELECT * FROM programas ORDER BY id ASC");
    if ($resultado) {
        $programas = $resultado->fetch_all(MYSQLI_ASSOC);
        $mostrarTituloLista = true;
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visualizar / Actualizar Programa</title>
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
    <link rel="stylesheet" href="actualiza_programa.css">
</head>
<body>
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank"></a>
</nav>

<h1>DATASENA</h1>
<img src="../../img/logo-sena.png" alt="Logo SENA" class="img">

<div class="form-container">
    <h2>Visualizar / Actualizar Programa</h2>
    <?php if ($mensaje): ?>
        <p style="color:<?= str_contains($mensaje, 'Error') || str_contains($mensaje, 'Ya existe') ? 'red' : 'green' ?>; font-weight:bold;"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <form method="GET" action="">
        <label for="nombre_programa">Buscar programa por nombre:</label>
        <input type="text" name="nombre_programa" id="nombre_programa" value="<?= isset($_GET['nombre_programa']) ? htmlspecialchars($_GET['nombre_programa']) : '' ?>" >
        <button type="submit" name="buscar" class="logout-btn">🔍 Buscar</button>
        <button type="submit" name="mostrar_todos" class="logout-btn">📋 Mostrar todos</button>
        <button type="button" class="logout-btn" onclick="window.location.href='../super_menu.html'">↩️ Regresar</button>
    </form>

    <hr>

    <?php if (!empty($programa['id'])): ?>
    <form class="form-grid" action="" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($programa['id']) ?>">
        <div class="form-row">
            <label for="nombre_programa">Nombre del programa:</label>
            <input type="text" id="nombre_programa" name="nombre_programa" value="<?= htmlspecialchars($programa['nombre_programa']) ?>" required>
        </div>
        <div class="form-row">
            <label for="tipo_programa">Tipo de programa:</label>
            <select id="tipo_programa" name="tipo_programa" required>
                <option value="Tecnico" <?= $programa['tipo_programa'] == 'Tecnico' ? 'selected' : '' ?>>Técnico</option>
                <option value="Tecnologo" <?= $programa['tipo_programa'] == 'Tecnologo' ? 'selected' : '' ?>>Tecnólogo</option>
                <option value="Operario" <?= $programa['tipo_programa'] == 'Operario' ? 'selected' : '' ?>>Operario</option>
            </select>
        </div>
        <div class="form-row">
            <label for="numero_ficha">Número de ficha:</label>
            <input type="text" id="numero_ficha" name="numero_ficha" value="<?= htmlspecialchars($programa['numero_ficha']) ?>" required>
        </div>
        <div class="form-row">
            <label for="duracion_programa">Duración en meses:</label>
            <input type="text" id="duracion_programa" name="duracion_programa" value="<?= htmlspecialchars($programa['duracion_programa']) ?>" required readonly>
        </div>
        <div class="form-row">
            <label for="activacion">Estado:</label>
            <select id="activacion" name="activacion" required>
                <option value="activo" <?= $programa['activacion'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= $programa['activacion'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
        <div class="form-row botones-finales">
            <button class="logout-btn" type="submit">Actualizar Programa</button>
        </div>
    </form>
    <?php endif; ?>

    <?php if (!empty($programas)): ?>
        <?php if ($mostrarTituloLista): ?>
            <h3>Lista de Programas</h3>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre del Programa</th>
                    <th>Tipo de Programa</th>
                    <th>Número de Ficha</th>
                    <th>Duración en meses</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($programas as $prog): ?>
                    <tr>
                        <td><?= htmlspecialchars($prog['nombre_programa']) ?></td>
                        <td><?= htmlspecialchars($prog['tipo_programa'] == 'Tecnico' ? 'Técnico' : ($prog['tipo_programa'] == 'Tecnologo' ? 'Tecnólogo' : 'Operario')) ?></td>
                        <td><?= htmlspecialchars($prog['numero_ficha']) ?></td>
                        <td><?= htmlspecialchars($prog['duracion_programa']) ?></td>
                        <td><?= htmlspecialchars($prog['activacion'] == 'activo' ? 'Activo' : 'Inactivo') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<footer>
    <a>&copy; 2025 Todos los derechos reservados - Proyecto SENA</a>
</footer>

<!-- Script para actualizar duración automáticamente -->
<script>
const tipoSelect = document.getElementById('tipo_programa');
const duracionInput = document.getElementById('duracion_programa');
const duracionPorTipo = { 'Tecnico': 12, 'Tecnologo': 24, 'Operario': 6 };

tipoSelect.addEventListener('change', function() {
    const tipo = tipoSelect.value;
    if (duracionPorTipo.hasOwnProperty(tipo)) {
        duracionInput.value = duracionPorTipo[tipo];
    }
});

window.addEventListener('load', function() {
    const tipo = tipoSelect.value;
    if (duracionPorTipo.hasOwnProperty(tipo)) {
        duracionInput.value = duracionPorTipo[tipo];
    }
});
</script>

<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank"></a>
</nav>
</body>
</html>
