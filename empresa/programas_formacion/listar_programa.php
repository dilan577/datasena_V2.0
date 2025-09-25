<?php
try {
    $conexion = new PDO("mysql:host=localhost;dbname=datasena_db", "root", "");
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexi&oacute;n: " . $e->getMessage());
}

$programa = null;
$programas = [];
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mostrar_todos'])) {
        $stmt = $conexion->query("SELECT id, nombre_programa, tipo_programa, numero_ficha, duracion_programa, activacion FROM programas ORDER BY id ASC");
        $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($programas)) {
            $mensaje = "❌ No hay programas registrados.";
        }
    } elseif (isset($_POST['buscar'])) {
        $busqueda = trim($_POST['nombre_buscar']);
        if ($busqueda === '') {
            $mensaje = "⚠️ Por favor ingrese un t&eacute;rmino para buscar.";
        } else {
            $sql = "SELECT id, nombre_programa, tipo_programa, numero_ficha, duracion_programa, activacion 
                    FROM programas 
                    WHERE nombre_programa LIKE :busqueda OR tipo_programa LIKE :busqueda";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':busqueda' => "%$busqueda%"]);
            $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($programas)) {
                $mensaje = "⚠️ No se encontr&oacute; programas con ese criterio.";
            }
        }
    }
}

$conexion = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Listar Programas</title>
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon" />
    <link rel="stylesheet" href="listar_programa.css" />
</head>
<body>

<!--barra del gov superior-->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<header>DATASENA</header>
<img src="../../img/logo-sena.png" alt="Logo SENA" class="img" />

<div class="form-container">
    <h2>Listar Programas</h2>

    <?php if (!empty($mensaje)): ?>
        <p class="mensaje-error"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <form method="POST" action="" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
        <label for="nombre_buscar">Buscar programa:</label>
        <input
            type="text"
            id="nombre_buscar"
            name="nombre_buscar"
            placeholder="Nombre o tipo de programa"
            value="<?= isset($_POST['nombre_buscar']) ? htmlspecialchars($_POST['nombre_buscar']) : '' ?>"
            <?= isset($_POST['mostrar_todos']) ? '' : 'required' ?>
        />
        <button class="logout-btn" type="submit" name="buscar">🔍 Buscar</button>
        <button class="logout-btn" type="submit" name="mostrar_todos" onclick="document.getElementById('nombre_buscar').removeAttribute('required');">📋 Mostrar Todos</button>
        <button type="button" class="logout-btn" onclick="window.location.href='../empresa_menu.html'">↩️ Regresar</button>
    </form>

    <hr />

<?php if (!empty($programas)): ?>
    <?php if (count($programas) === 1): ?>
        <!-- Mostrar un programa en vertical -->
        <?php $p = $programas[0]; ?>
        <div class="programa-card">
            <p><strong>Nombre del Programa:</strong> <?= htmlspecialchars($p['nombre_programa']) ?></p>
            <p><strong>Tipo de Programa:</strong> <?= htmlspecialchars($p['tipo_programa']) ?></p>
            <p><strong>Número de Ficha:</strong> <?= htmlspecialchars($p['numero_ficha']) ?></p>
            <p><strong>Duraci&oacute;n:</strong> <?= htmlspecialchars($p['duracion_programa']) ?></p>
            <p><strong>Activaci&oacute;n:</strong> <?= htmlspecialchars($p['activacion']) ?></p>
        </div>
    <?php else: ?>
        <!-- Mostrar varios programas en tabla -->
        <h3>📋 Programas Registrados</h3>
        <div class="user-list" style="overflow-x:auto;">
            <table border="1" cellpadding="6" cellspacing="0" style="width:100%; background:#fff; border-collapse: collapse;">
                <thead style="background-color: #0078c0; color: white;">
                    <tr>
                        <th>Nombre del Programa</th>
                        <th>Tipo de Programa</th>
                        <th>Número de Ficha</th>
                        <th>Duraci&oacute;n</th>
                        <th>Activaci&oacute;n</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($programas as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nombre_programa']) ?></td>
                            <td><?= htmlspecialchars($p['tipo_programa']) ?></td>
                            <td><?= htmlspecialchars($p['numero_ficha']) ?></td>
                            <td><?= htmlspecialchars($p['duracion_programa']) ?></td>
                            <td><?= htmlspecialchars($p['activacion']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endif; ?>
</div>

<footer>
    <a>&copy; Todos los derechos reservados al SENA</a>
</footer>

<!--barra del gov inferior-->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra inferior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>
</body>
</html>
