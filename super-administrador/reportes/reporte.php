<?php
// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=datasena_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo_reporte'];
    $id = $_POST['id_elemento'];
    $observacion = $_POST['observacion'];

    $stmt = $pdo->prepare("INSERT INTO reportes (tipo_reporte, id_referenciado, observacion) VALUES (?, ?, ?)");
    $stmt->execute([$tipo, $id, $observacion]);

    $mensaje = "✅ Reporte guardado exitosamente. Puedes descargarlo:";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportar Elemento</title>
    <link rel="stylesheet" href="reporte.css">
</head>
<body>
    <div class="barra-gov">.gov.co</div>

    <div class="contenedor">
        <h1>📋 Reportar Empresa, Administrador o Programa</h1>
        <form method="POST">
            <label>Tipo de reporte:</label>
            <select name="tipo_reporte" required>
                <option value="">Seleccione</option>
                <option value="empresa">Empresa</option>
                <option value="administrador">Administrador</option>
                <option value="programa">Programa</option>
            </select>

            <label>ID del elemento (empresa/admin/programa):</label>
            <input type="number" name="id_elemento" required>

            <label>Observación:</label>
            <textarea name="observacion" rows="5" required></textarea>

            <button type="submit">Guardar Reporte</button>
        </form>

        <?php if ($mensaje): ?>
            <div class="mensaje"><?= $mensaje ?></div>
            <div class="descargas">
                <a href="descargar_pdf.php?id=<?= $pdo->lastInsertId() ?>" target="_blank">📄 Descargar PDF</a> |
                <a href="descargar_xml.php?id=<?= $pdo->lastInsertId() ?>" target="_blank">📦 Descargar XML</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="barra-gov">.gov.co</div>
</body>
</html>
