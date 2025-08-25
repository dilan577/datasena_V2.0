<?php
$conexion = new mysqli("localhost", "root", "", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$programa = null;
$todos = [];
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

// Prioridad absoluta: si se presionó "Mostrar Todos"
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['mostrar_todos'])) {
    $sql = "SELECT * FROM programas";
    $result = $conexion->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($fila = $result->fetch_assoc()) {
            $todos[] = $fila;
        }
    } else {
        $mensaje = "❌ No hay programas registrados.";
        $mensaje_tipo = "info";
    }
}
// Si NO se presionó "Mostrar Todos" y hay búsqueda individual
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['mostrar_todos']) && !empty($_GET['numero_ficha'])) {
    $numero_ficha = $_GET['numero_ficha'];
    $stmt = $conexion->prepare("SELECT * FROM programas WHERE numero_ficha = ?");
    $stmt->bind_param("s", $numero_ficha);
    $stmt->execute();
    $result = $stmt->get_result();
    $programa = $result->fetch_assoc();
    $stmt->close();

    if (!$programa) {
        $mensaje = "❌ Programa no encontrado.";
        $mensaje_tipo = "error";
    }
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Habilitar/Inhabilitar Programa</title>
    <link rel="stylesheet" href="../../administrador/admin_programas_formacion/admin_habili_inhabilit_programa.css" />
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>

<!--barra del gov superior-->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

    <h1>DATASENA</h1>
    <img src="../../img/logo-sena.png" alt="Logo SENA" class="img" />

    <header>
        <h1>Panel de Habilitación de Programas</h1>
    </header>

    <main>
        <div class="search-box">
            <form method="get" class="form-flex">
                <label for="buscar_ficha">Número de Ficha:</label>
                <input type="text" id="buscar_ficha" name="numero_ficha" value="<?= htmlspecialchars($_GET['numero_ficha'] ?? '') ?>" />
                <button type="submit">🔍 Buscar</button>
                <button type="submit" name="mostrar_todos">📋 Mostrar Todos</button>
                <button type="button" onclick="location.href='../admin_menu.html'">↩️ Regresar</button>
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
                    </div>
                </form>
            </section>
            <?php elseif (!empty($_GET['numero_ficha']) && !$programa && !isset($_GET['mostrar_todos'])): ?>
                <p class="mensaje error">❌ Programa no encontrado.</p>
            <?php elseif (empty($todos) && !isset($_GET['mostrar_todos'])): ?>
            <p class="mensaje info">🧭 Ingrese un número de ficha o use "Mostrar Todos".</p>
        <?php endif; ?>

        <?php if (!empty($todos)): ?>
            <h3>📋 Programas Registrados</h3>
            <div style="overflow-x:auto;">
                <table border="1" cellpadding="6" cellspacing="0" style="width:100%; background:#fff; border-collapse: collapse;">
                    <thead style="background-color: #0078c0; color: white;">
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Número Ficha</th>
                            <th>Duración</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todos as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['nombre_programa']) ?></td>
                                <td><?= htmlspecialchars($p['tipo_programa']) ?></td>
                                <td><?= htmlspecialchars($p['numero_ficha']) ?></td>
                                <td><?= htmlspecialchars($p['duracion_programa']) ?></td>
                                <td><?= $p['activacion'] === 'activo' ? '✅ Habilitado' : '❌ Inhabilitado' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 Todos los derechos reservados - Proyecto SENA</p>
    </footer>

    <!--barra del gov inferior-->
    <nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
    <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
    </nav>
</body>
</html>