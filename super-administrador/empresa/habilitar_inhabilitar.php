<?php
session_start();

// Validar que esté logueado y que sea superadministrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'super') {
    header("Location: ../inicio_sesion.html");
    exit();
}
$conexion = new mysqli("localhost", "root", "", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$empresas = null;
$todas_empresas = [];
$mensaje = "";
$mensaje_tipo = "";

// Actualizar estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_estado'])) {
    $cc = $_POST['cc'] ?? '';
    $nuevo_estado = $_POST['nuevo_estado'] ?? '';
    
    if (!empty($cc) && !empty($nuevo_estado)) {
        $stmt = $conexion->prepare("UPDATE empresas SET estado_habilitacion = ? WHERE numero_identidad = ?");
        $stmt->bind_param("ss", $nuevo_estado, $cc);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $mensaje = "✅ Estado actualizado correctamente.";
            $mensaje_tipo = "exito";
        } else {
            $mensaje = "⚠️ No se encontró la empresa o no hubo cambios.";
            $mensaje_tipo = "error";
        }
        $stmt->close();
    }
}

// Buscar empresa solo si NO es mostrar todos y el campo cc no está vacío
if (!isset($_GET['mostrar_todos']) && !empty($_GET['cc'])) {
    $cc = $_GET['cc'];
    $stmt = $conexion->prepare("SELECT * FROM empresas WHERE numero_identidad = ?");
    $stmt->bind_param("s", $cc);
    $stmt->execute();
    $result = $stmt->get_result();
    $empresas = $result->fetch_assoc();
    $stmt->close();
    
    if (!$empresas) {
        $mensaje = "❌ Empresa no encontrada.";
        $mensaje_tipo = "error";
    }
}

// Mostrar todas
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['mostrar_todos'])) {
    $sql = "SELECT * FROM empresas";
    $resultado = $conexion->query($sql);
    
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $todas_empresas[] = $fila;
        }
    } else {
        $mensaje = "❌ No hay empresas registradas.";
        $mensaje_tipo = "info";
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
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
    <link rel="stylesheet" href="habilitar_inhabilitar.css">
</head>
<body>
<!--barra del gov superior-->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>

<h1>DATASENA</h1>

<header class="encabezado">
    <img src="../../img/logo-sena.png"  class="img" alt="img">
    <h1>Panel de Habilitación de Empresas</h1>
</header>

<main>
    <div class="search-box">
        <form method="get">
            <label for="buscar_cc">Número de Documento:</label>
            <input type="text" id="buscar_cc" name="cc" placeholder="Ejemplo: 123456789">
            <button type="submit">🔍 Buscar</button>
            <button type="submit" name="mostrar_todos">📋 Mostrar Todas</button>
            <button type="button" class="logout-btn" onclick="window.location.href='../super_menu.html'">↩️ Regresar</button>
        </form>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="mensaje <?= $mensaje_tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    
    <!-- Mostrar empresa encontrada -->
    <?php if ($empresas): ?>
        <section class="empresa-detalle">
            <h2>Datos de la Empresa</h2>
            <ul>
                <li><strong>Tipo de Documento:</strong> <?= htmlspecialchars($empresas['tipo_documento']) ?></li>
                <li><strong>Número de Documento:</strong> <?= htmlspecialchars($empresas['numero_identidad']) ?></li>
                <li><strong>Nombre de la Empresa:</strong> <?= htmlspecialchars($empresas['nickname']) ?></li>
                <li><strong>Teléfono:</strong> <?= htmlspecialchars($empresas['telefono']) ?></li>
                <li><strong>Correo Electrónico:</strong> <?= htmlspecialchars($empresas['correo']) ?></li>
                <li><strong>Dirección:</strong> <?= htmlspecialchars($empresas['direccion']) ?></li>
                <li><strong>Actividad Económica:</strong> <?= htmlspecialchars($empresas['actividad_economica']) ?></li>
                <li><strong>Estado Actual:</strong> <?= $empresas['estado_habilitacion'] === 'Activo' ? '✅ Habilitada' : '❌ Inhabilitada' ?></li>
                <li><strong>Fecha de Registro:</strong> <?= htmlspecialchars($empresas['fecha_registro']) ?></li>
            </ul>
            
            <form method="post" class="form-estado">
                <input type="hidden" name="cc" value="<?= htmlspecialchars($empresas['numero_identidad']) ?>">
                <label for="nuevo_estado">Cambiar Estado: </label>
                <select name="nuevo_estado" required>
                    <option value="">Seleccione...</option>
                    <option value="Activo">✅ Habilitar</option>
                    <option value="Inactivo">❌ Inhabilitar</option>
                </select>
                <div class="botones">
                    <button type="submit" name="actualizar_estado">Actualizar Estado</button>
                </div>
            </form>
        </section>
    <?php elseif (!empty($_GET['cc']) && !$empresas): ?>
        <p class="mensaje error">❌ Empresa no encontrada.</p>
    <?php elseif (empty($todas_empresas)): ?>
        <p class="mensaje info">🧭 Ingrese un número de documento para buscar una empresa.</p>
    <?php endif; ?>

    <?php if (!empty($todas_empresas)): ?>
        <h2>📋 Todas las Empresas</h2>
        <div class="tabla-contenedor">
            <table border="1" cellpadding="6" cellspacing="0">
                <thead>
                    <tr>
                        <th>Tipo Doc</th>
                        <th>Identidad</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Dirección</th>
                        <th>Actividad</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todas_empresas as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['tipo_documento']) ?></td>
                            <td><?= htmlspecialchars($e['numero_identidad']) ?></td>
                            <td><?= htmlspecialchars($e['nickname']) ?></td>
                            <td><?= htmlspecialchars($e['telefono']) ?></td>
                            <td><?= htmlspecialchars($e['correo']) ?></td>
                            <td><?= htmlspecialchars($e['direccion']) ?></td>
                            <td><?= htmlspecialchars($e['actividad_economica']) ?></td>
                            <td><?= htmlspecialchars($e['estado_habilitacion']) ?></td>
                            <td><?= htmlspecialchars($e['fecha_registro']) ?></td>
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