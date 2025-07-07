<?php
$conexion = new mysqli("localhost", "root", "123456", "datasena_db");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$reportes = [];
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo_reporte'])) {
    $tipo = $_POST['tipo_reporte'];

    if (!in_array($tipo, ['empresa', 'admin', 'programa'])) {
        $mensaje = "⚠️ Tipo de reporte inválido.";
    } else {
        $stmt = $conexion->prepare("SELECT * FROM reportes WHERE tipo_reporte = ?");
        $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
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
    <title>📋 Ver Reportes</title>
    <link rel="stylesheet" href="listar_reporte.css">
</head>
<body>
    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
    </div>

    <header>DATASENA - Ver Reportes</header>
    <img src="../../img/logo-sena.png" alt="Logo SENA" class="img">

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
                    <input type="radio" name="tipo_reporte" value="admin">
                    <span class="label-text"><span class="icon">👤</span> Admin</span>
                </label>
                <label class="radio-option">
                    <input type="radio" name="tipo_reporte" value="programa">
                    <span class="label-text"><span class="icon">📚</span> Programa</span>
                </label>
            </div>
            <button class="logout-btn" type="submit">🔍 Buscar</button>
            <button class="logout-btn" type="button" onclick="window.location.href='menu_su.php'">↩️ Regresar</button>
        </form>

        <?php if (!empty($mensaje)): ?>
            <p class="mensaje-error"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <?php foreach ($reportes as $r): ?>
            <div class="empresa-card">
                <p><strong>🆔 ID del Reporte:</strong> <?= $r['id'] ?></p>
                <p><strong>📂 Tipo:</strong> <?= ucfirst($r['tipo_reporte']) ?></p>
                <p><strong>🔗 ID Referenciado:</strong> <?= $r['id_referenciado'] ?></p>
                <p><strong>📝 Observación:</strong> <?= htmlspecialchars($r['observacion']) ?></p>
                <p><strong>📅 Fecha:</strong> <?= $r['fecha_reporte'] ?></p>
                <p>
                    <a class="logout-btn" style="background-color:#28a745;" href="descargar_pdf.php?id=<?= $r['id'] ?>" target="_blank">📄 PDF</a>
                    <a class="logout-btn" style="background-color:#6c757d;" href="descargar_xml.php?id=<?= $r['id'] ?>" target="_blank">📦 XML</a>
                </p>
            </div>
        <?php endforeach; ?>
    </div>

    <footer>
        <a>&copy; Todos los derechos reservados al SENA</a>
    </footer>

    <div class="barra-gov">
        <img src="../../img/gov.png" alt="Gobierno de Colombia" class="gov-logo">
    </div>
</body>
</html>
