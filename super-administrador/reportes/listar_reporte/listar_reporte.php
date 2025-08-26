<?php
$conexion = new mysqli("localhost", "root", "", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$reportes = [];
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo_reporte'])) {
    $tipo = $_POST['tipo_reporte'];

    // Validar tipo
    if (!in_array($tipo, ['empresa', 'administrador', 'programa'])) {
        $mensaje = "⚠️ Tipo de reporte inválido.";
    } else {
        // Consultar reportes del tipo seleccionado
        $stmt = $conexion->prepare("SELECT * FROM reportes WHERE tipo_reporte = ?");
        $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            // Traer nombres para mostrar
            // Dependiendo del tipo, se obtiene el nombre de la tabla correspondiente
            $tabla = '';
            $campo_nombre = '';
            if ($tipo === 'empresa') {
                $tabla = 'empresas';
                $campo_nombre = 'nickname';
            } elseif ($tipo === 'administrador') {
                $tabla = 'admin';
                $campo_nombre = "CONCAT(nombres, ' ', apellidos, ' (', nickname, ')')";
            } elseif ($tipo === 'programa') {
                $tabla = 'programas';
                $campo_nombre = 'nombre_programa';
            }

            while ($fila = $resultado->fetch_assoc()) {
                // Obtener nombre real
                $id_ref = $fila['id_referenciado'];

                // Consulta para obtener el nombre
                $sql_nombre = "SELECT $campo_nombre AS nombre FROM $tabla WHERE id = ?";
                $stmt_nombre = $conexion->prepare($sql_nombre);
                $stmt_nombre->bind_param("i", $id_ref);
                $stmt_nombre->execute();
                $res_nombre = $stmt_nombre->get_result();
                $nombre_real = $res_nombre->fetch_assoc()['nombre'] ?? 'Nombre no encontrado';
                $stmt_nombre->close();

                $fila['nombre_referenciado'] = $nombre_real;
                $reportes[] = $fila;
            }
        } else {
            $mensaje = "⚠️ No hay reportes registrados para este tipo.";
        }

        $stmt->close();
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Reportes</title>
    <link rel="shortcut icon" href="../../../img/Logotipo_Datasena.png" type="image/x-icon">
    <link rel="stylesheet" href="listar_reporte.css">
</head>
<body>

    <!--barra del gov superior-->
    <nav class="navbar navbar-expand-lg barra-superior-govco" aria-label="Barra superior">
    <a href="https://www.gov.co/" target="_blank" aria-label="Portal del Estado Colombiano - GOV.CO"></a>
    </nav>

    <header>DATASENA <br> Ver Reportes</header>
    <img src="../../../img/logo-sena.png" alt="Logo SENA" class="img">

    <div class="form-container">
        <h2>📁 Buscar Reportes por Tipo</h2>

        <form method="post" style="margin-bottom: 30px;">
            <label>Selecciona tipo de reporte:</label>
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="tipo_reporte" value="empresa" required>
                    <span class="label-text"><span class="icon">🏢</span> Empresa</span>
                </label>
                <label class="radio-option">
                    <input type="radio" name="tipo_reporte" value="administrador">
                    <span class="label-text"><span class="icon">👤</span> Administrador</span>
                </label>
                <label class="radio-option">
                    <input type="radio" name="tipo_reporte" value="programa">
                    <span class="label-text"><span class="icon">📚</span> Programa</span>
                </label>
            </div>
            <button class="logout-btn" type="submit">🔍 Buscar</button>
            <button class="logout-btn" type="button" onclick="window.location.href='../../super_menu.html'">↩️ Regresar</button>
        </form>

        <?php if (!empty($mensaje)): ?>
            <p class="mensaje-error"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <?php foreach ($reportes as $r): ?>
            <div class="empresa-card">
                <p><strong>🆔 ID del Reporte:</strong> <?= $r['id'] ?></p>
                <p><strong>📂 Tipo:</strong> <?= ucfirst($r['tipo_reporte']) ?></p>
                <p><strong>🔗 ID Referenciado:</strong> <?= $r['id_referenciado'] ?></p>
                <p><strong>👤 Nombre Referenciado:</strong> <?= htmlspecialchars($r['nombre_referenciado']) ?></p>
                <p><strong>📝 Observación:</strong> <?= htmlspecialchars($r['observacion']) ?></p>
                <p><strong>📅 Fecha:</strong> <?= $r['fecha_reporte'] ?></p>
                <p>
                    <a class="logout-btn" style="background-color:#28a745;" href="../descargas_pdf.php?id=<?= $r['id'] ?>" target="_blank">📄 PDF</a>
                    <a class="logout-btn" style="background-color:#6c757d;" href="../descargas_xml.php?id=<?= $r['id'] ?>" target="_blank">📦 XML</a>
                </p>
            </div>
        <?php endforeach; ?>
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
