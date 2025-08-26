<?php
$conexion = new mysqli("localhost", "root", "", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$admin = null;
$todos = [];
$mensaje = "";
$mensaje_tipo = "";

// Actualizar estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_estado'])) {
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nuevo_estado = $_POST['nuevo_estado'] ?? '';

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

// Búsqueda individual
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['numero_documento']) && !isset($_GET['mostrar_todos'])) {
    $numero_documento = $_GET['numero_documento'];
    $stmt = $conexion->prepare("SELECT * FROM admin WHERE numero_documento = ?");
    $stmt->bind_param("s", $numero_documento);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();
    if (!$admin) {
        $mensaje = "❌ Administrador no encontrado.";
        $mensaje_tipo = "error";
    }
}

// Mostrar todos
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['mostrar_todos'])) {
    $sql = "SELECT * FROM admin";
    $result = $conexion->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($fila = $result->fetch_assoc()) {
            $todos[] = $fila;
        }
    } else {
        $mensaje = "❌ No hay administradores registrados.";
        $mensaje_tipo = "error";
    }
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Habilitar/Inhabilitar Administrador</title>
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
    <link rel="stylesheet" href="habilitar_admin.css">
</head>
<body>
    
<!--barra del gov superior-->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

    <h1>DATASENA</h1>
    <img src="../../img/logo-sena.png" alt="Logo SENA" class="img">

    <header>
        <h1>Panel de Habilitación de Administradores</h1>
    </header>

    <main>
        <div class="search-box">
            <form method="get">
                <label for="buscar_doc">Número de Documento:</label>
                <input type="text" id="buscar_doc" name="numero_documento">
                <button type="submit">🔍 Buscar</button>
                <button type="submit" name="mostrar_todos">📋 Mostrar Todos</button>
                <button type="button" class="logout-btn" onclick="window.location.href='../super_menu.html'">↩️ Regresar</button>
            </form>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje <?= $mensaje_tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <!-- Individual -->
        <?php if ($admin): ?>
            <section class="empresa-detalle">
                <h2>Datos del Administrador</h2>
                <ul>
                    <li><strong>Nombre completo:</strong> <?= htmlspecialchars($admin['nombres'] . ' ' . $admin['apellidos']) ?></li>
                    <li><strong>Documento:</strong> <?= htmlspecialchars($admin['numero_documento']) ?></li>
                    <li><strong>Correo:</strong> <?= htmlspecialchars($admin['correo_electronico']) ?></li>
                    <li><strong>Nickname:</strong> <?= htmlspecialchars($admin['nickname']) ?></li>
                    <li><strong>Fecha de creación:</strong> <?= htmlspecialchars($admin['fecha_creacion']) ?></li>
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
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <!-- Todos -->
        <?php if (!empty($todos)): ?>
            <h3>📋 Administradores Registrados</h3>
            <div style="overflow-x:auto;">
                <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; background: #fff;">
                    <thead style="background-color: #0078c0; color: white;">
                        <tr>
                            <th>Tipo Doc</th>
                            <th>Documento</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Correo</th>
                            <th>Nickname</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todos as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars($a['tipo_documento']) ?></td>
                                <td><?= htmlspecialchars($a['numero_documento']) ?></td>
                                <td><?= htmlspecialchars($a['nombres']) ?></td>
                                <td><?= htmlspecialchars($a['apellidos']) ?></td>
                                <td><?= htmlspecialchars($a['correo_electronico']) ?></td>
                                <td><?= htmlspecialchars($a['nickname']) ?></td>
                                <td><?= htmlspecialchars($a['estado_habilitacion']) ?></td>
                                <td><?= htmlspecialchars($a['fecha_creacion']) ?></td>
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

    <!--barra del gov superior-->
    <nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
    <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
    </nav>
</body>
</html>