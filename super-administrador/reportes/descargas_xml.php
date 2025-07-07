<?php
header("Content-type: text/xml");
$pdo = new PDO("mysql:host=localhost;dbname=datasena_db", "root", "123456");

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM reportes WHERE id = ?");
$stmt->execute([$id]);
$reporte = $stmt->fetch();

if (!$reporte) {
    die("<error>Reporte no encontrado</error>");
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<reporte>\n";
echo "  <tipo>{$reporte['tipo_reporte']}</tipo>\n";
echo "  <id_referenciado>{$reporte['id_referenciado']}</id_referenciado>\n";
echo "  <observacion>" . htmlspecialchars($reporte['observacion']) . "</observacion>\n";
echo "  <fecha>{$reporte['fecha_reporte']}</fecha>\n";
echo "</reporte>";
