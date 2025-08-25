<?php
require('fpdf/fpdf.php');
$pdo = new PDO("mysql:host=localhost;dbname=datasena_db", "root", "");

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM reportes WHERE id = ?");
$stmt->execute([$id]);
$reporte = $stmt->fetch();

if (!$reporte) {
    die("Reporte no encontrado");
}

class PDF extends FPDF {
    // Encabezado
    function Header() {
        // Logo opcional
        $this->Image('logo-sena.png', 10, 6, 20);

        $this->SetFont('Arial','B',16);
        $this->SetFillColor(52, 152, 219); // Azul
        $this->SetTextColor(255,255,255);
        $this->Cell(0,15,'Reporte DATASENA',0,1,'C',true);
        $this->Ln(5);
    }

    // Pie de página
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0);

// Cabecera de la tabla
$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(60,10,'Campo',1,0,'C',true);
$pdf->Cell(130,10,'Valor',1,1,'C',true);

$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0);

// Fila: Tipo de reporte
$pdf->Cell(60,10,'Tipo de reporte',1,0,'L');
$pdf->Cell(130,10,utf8_decode($reporte['tipo_reporte']),1,1,'L');

// Fila: ID Referenciado
$pdf->Cell(60,10,'ID Referenciado',1,0,'L');
$pdf->Cell(130,10,$reporte['id_referenciado'],1,1,'L');

// Fila: Observación (varias líneas)
$pdf->Cell(60,10,'Observacion',1,0,'L');

// Guardar posición
$x = $pdf->GetX();
$y = $pdf->GetY();

// Crear MultiCell para el valor
$pdf->MultiCell(130,10,utf8_decode($reporte['observacion']),1,'L');

// Ajustar si la observación ocupa varias líneas
$pdf->SetXY($x + 130, $y);

// Fila: Fecha
$pdf->Cell(60,10,'Fecha',1,0,'L');
$pdf->Cell(130,10,$reporte['fecha_reporte'],1,1,'L');

$pdf->Output();
