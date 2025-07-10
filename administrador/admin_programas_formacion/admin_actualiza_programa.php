<?php
$conexion = new mysqli("localhost", "root", "", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$mensaje = "";
$programa = null;
$programas = []; // Para guardar varios resultados

// Control para mostrar título y tabla con todos los programas
$mostrarTituloLista = false;

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

// Procesar búsqueda o mostrar todos
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['buscar'])) {
    // Quitamos espacios sobrantes para la búsqueda
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

        // Si solo hay 1 resultado, cargarlo en el formulario, y no mostrar tabla
        if (count($programas) === 1) {
            $programa = $programas[0];
            $programas = []; // vaciar array para no mostrar tabla
        } else {
            // Si hay más de 1 resultado, no mostrar formulario y tampoco tabla ni título
            $programa = null;
            $programas = [];
            $mensaje = "Se encontraron " . count($programas) . " resultados. Por favor refine su búsqueda para editar.";
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
    <link rel="stylesheet" href="../../administrador/admin_programas_formacion/admin_actualiza_programa.css">
</head>
<body>
    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
    </div>
    <h1>DATASENA</h1>
    <img src="../../img/logo-sena.png" alt="Logo SENA" class="img">

    <div class="form-container">
        <h2>Visualizar / Actualizar Programa</h2>

        <?php if ($mensaje): ?>
            <p style="color:<?= str_contains($mensaje, 'Error') ? 'red' : 'green' ?>; font-weight:bold;"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <form method="GET" action="">
            <label for="nombre_programa">Buscar Programa por Nombre:</label>
            <input type="text" name="nombre_programa" id="nombre_programa" value="<?= isset($_GET['nombre_programa']) ? htmlspecialchars($_GET['nombre_programa']) : '' ?>" >
            <button type="submit" name="buscar" class="logout-btn">🔍 Buscar</button>
            <button type="submit" name="mostrar_todos" class="logout-btn">📋 Mostrar todos</button>
            <button type="button" class="logout-btn" onclick="window.location.href='../admin_menu.html'">↩️ Regresar</button>
        </form>

        <hr>

        <?php if (!empty($programa['id'])): ?>
            <form class="form-grid" action="" method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($programa['id']) ?>">

                <div class="form-row">
                    <label for="nombre_programa">Nombre del Programa:</label>
                    <input type="text" id="nombre_programa" name="nombre_programa" value="<?= htmlspecialchars($programa['nombre_programa']) ?>" required>
                </div>

                <div class="form-row">
                    <label for="tipo_programa">Tipo de Programa:</label>
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
                    <label for="duracion_programa">Duración:</label>
                    <input type="text" id="duracion_programa" name="duracion_programa" value="<?= htmlspecialchars($programa['duracion_programa']) ?>" required>
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
                        <th>ID</th>
                        <th>Nombre del Programa</th>
                        <th>Tipo de Programa</th>
                        <th>Número de Ficha</th>
                        <th>Duración</th>
                        <th>Activación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($programas as $prog): ?>
                        <tr>
                            <td><?= htmlspecialchars($prog['id']) ?></td>
                            <td><?= htmlspecialchars($prog['nombre_programa']) ?></td>
                            <td><?= htmlspecialchars($prog['tipo_programa']) ?></td>
                            <td><?= htmlspecialchars($prog['numero_ficha']) ?></td>
                            <td><?= htmlspecialchars($prog['duracion_programa']) ?></td>
                            <td><?= htmlspecialchars($prog['activacion']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>

    <footer>
        <a>&copy;  2025 Todos los derechos reservados - Proyecto SENA</a>
    </footer>

    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
    </div>
</body>
</html>
