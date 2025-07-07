<?php
require('fpdf/fpdf.php');
$pdo = new PDO("mysql:host=localhost;dbname=datasena_db", "root", "123456");

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM reportes WHERE id = ?");
$stmt->execute([$id]);
$reporte = $stmt->fetch();

if (!$reporte) {
    die("Reporte no encontrado");
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Reporte DATASENA',0,1,'C');

$pdf->SetFont('Arial','',12);
$pdf->Ln(10);
$pdf->MultiCell(0,10,"Tipo de reporte: " . $reporte['tipo_reporte']);
$pdf->MultiCell(0,10,"ID Referenciado: " . $reporte['id_referenciado']);
$pdf->MultiCell(0,10,"Observación:\n" . $reporte['observacion']);
$pdf->MultiCell(0,10,"Fecha: " . $reporte['fecha_reporte']);

$pdf->Output();
