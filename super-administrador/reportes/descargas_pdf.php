<?php
require('fpdf/fpdf.php');

class PDF extends FPDF
{
    function Header()
    {
        // Borde gris alrededor de la hoja
        $this->SetDrawColor(169,169,169);
        $this->Rect(5,5,200,287,'D'); 

        // Encabezado con título
        $this->SetFont('Arial','B',16);
        $this->SetFillColor(52, 152, 219);
        $this->SetTextColor(255,255,255);
        $this->Cell(0,15,'Reporte DATASENA',0,1,'C',true);
        $this->Ln(5);
    }

    function Footer()
    {
        // Número de página en la parte inferior
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

// Validar que llegue el ID por GET
$id = $_GET['id'] ?? null;
if (!$id) die("❌ No se especificó el reporte");

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=datasena_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Traer datos del reporte
$stmt = $pdo->prepare("SELECT tipo_reporte, id_referenciado, observacion FROM reportes WHERE id = ?");
$stmt->execute([$id]);
$reporte = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reporte) die("❌ Reporte no encontrado");

// Crear PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// Mostrar datos según tipo de reporte
$pdf->Cell(50,10,'Tipo de Reporte:');
$pdf->Cell(100,10,$reporte['tipo_reporte'],0,1);

$pdf->Cell(50,10,'ID Referenciado:');
$pdf->Cell(100,10,$reporte['id_referenciado'],0,1);

// Observación real del formulario
$pdf->Cell(50,10,'Observacion:');
$pdf->MultiCell(0,10,$reporte['observacion'],0,1);

// Fecha del reporte
$pdf->Ln(5);
$pdf->Cell(50,10,'Fecha:');
$pdf->Cell(100,10,date("d/m/Y"),0,1);

// Generar PDF
$pdf->Output();
?>
