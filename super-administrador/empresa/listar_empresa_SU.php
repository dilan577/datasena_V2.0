<?php
$conexion = new mysqli("localhost", "root", "", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$empresa = null;
$todas_empresas = [];
$mensaje = "";

// Buscar empresa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $dato = trim($_POST['dato_busqueda']);
    $sql = "SELECT * FROM empresas WHERE numero_identidad = ? OR nickname = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $dato, $dato);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows > 0) {
        $empresa = $resultado->fetch_assoc();
    } else {
        $mensaje = "⚠️ No se encontró ninguna empresa con ese número de identidad o nombre.";
    }
    $stmt->close();
}

// Mostrar todas las empresas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mostrar_todos'])) {
    $sql = "SELECT * FROM empresas";
    $resultado = $conexion->query($sql);
    if ($resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $todas_empresas[] = $fila;
        }
    } else {
        $mensaje = "⚠️ No hay empresas registradas.";
    }
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Empresas</title>
    <link rel="shortcut icon" href="../../img/Logotipo_Datasena.png" type="image/x-icon">
    <link rel="stylesheet" href="../../administrador/admin_empresa/admin_listar_empresa_su_v2.css">

</head>
<body>
<!--barra del gov superior-->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>
    <header class="encabezado">
        <h1>DATASENA</h1>
        <img src="../../img/logo-sena.png" alt="Logo SENA" class="img" />
    </header>
    <div class="form-container">
        <h2>Listar Empresas</h2>
        <?php if (!empty($mensaje)): ?>
            <p class="mensaje-error"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>
        <form action="listar_empresa_su.php" method="post" style="display:flex; gap: 10px; flex-wrap:wrap;">
            <label for="buscar_dato">Buscar empresa:</label>
            <input type="text" id="buscar_dato" name="dato_busqueda" placeholder="Número de identidad o nombre de la empresa" required>
            <button class="logout-btn" type="submit" name="buscar">🔍 Buscar</button>
            <button class="logout-btn" type="submit" name="mostrar_todos" onclick="document.getElementById('buscar_dato').removeAttribute('required')">📋 Mostrar Todas</button>
            <button class="logout-btn" onclick="window.location.href='../super_menu.php'">↩️ Regresar</button>
        </form>
        <hr>
        <?php if (!empty($empresa)): ?>
            <div class="empresa-card">
                <p><strong>Tipo de documento:</strong> <?= htmlspecialchars($empresa['tipo_documento']) ?></p>
                <p><strong>Número de identidad:</strong> <?= htmlspecialchars($empresa['numero_identidad']) ?></p>
                <p><strong>Nombre de la empresa:</strong> <?= htmlspecialchars($empresa['nickname']) ?></p>
                <p><strong>Teléfono:</strong> <?= htmlspecialchars($empresa['telefono']) ?></p>
                <p><strong>Correo electrónico:</strong> <?= htmlspecialchars($empresa['correo']) ?></p>
                <p><strong>Dirección:</strong> <?= htmlspecialchars($empresa['direccion']) ?></p>
                <p><strong>Actividad económica:</strong> <?= htmlspecialchars($empresa['actividad_economica']) ?></p>
                <p><strong>Estado:</strong> <?= $empresa['estado'] == 1 ? 'Activo' : 'Inactivo' ?></p>
                <p><strong>Fecha de registro:</strong> <?= htmlspecialchars($empresa['fecha_registro']) ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($todas_empresas)): ?>
            <h3>📋 Lista de Empresas Registradas</h3>
            <div style="overflow-x:auto;">
                <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; background:#fff;">
                    <thead style="background-color: #0078c0; color: white;">
                        <tr>
                            <th>Tipo de Documento</th>
                            <th>Número de Identidad</th>
                            <th>Nombre de la Empresa</th>
                            <th>Teléfono</th>
                            <th>Correo Electrónico</th>
                            <th>Dirección</th>
                            <th>Actividad Económica</th>
                            <th>Estado</th>
                            <th>Fecha de Registro</th>
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
                                <td><?= $e['estado'] == 1 ? 'Activo' : 'Inactivo' ?></td>
                                <td><?= htmlspecialchars($e['fecha_registro']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <footer>
        <a>&copy; 2025 Todos los derechos reservados - Proyecto SENA</a>
    </footer>
<!--barra del gov inferior-->
<nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
  <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
</nav>
</body>
</html>