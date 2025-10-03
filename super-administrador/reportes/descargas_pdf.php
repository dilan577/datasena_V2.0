<?php
// -------------------------------
// Cargar librería FPDF
// -------------------------------
require('fpdf/fpdf.php');

// -------------------------------
// Conexión a la base de datos usando PDO
// -------------------------------
$pdo = new PDO("mysql:host=localhost;dbname=datasena_db", "root", "");

// -------------------------------
// Obtener ID del reporte desde la URL
// -------------------------------
$id = $_GET['id'] ?? 0;

// -------------------------------
// Traer reporte de la base de datos
// -------------------------------
$stmt = $pdo->prepare("SELECT * FROM reportes WHERE id = ?");
$stmt->execute([$id]);
$reporte = $stmt->fetch();

// Verificar si existe el reporte
if (!$reporte) {
    die("Reporte no encontrado");
}

// -------------------------------
// Clase PDF personalizada
// -------------------------------
class PDF extends FPDF {

    // -------------------------------
    // Encabezado
    // -------------------------------
    function Header() {
        $this->SetFont('Arial','B',16);
        $this->SetFillColor(52, 152, 219); // Azul
        $this->SetTextColor(255,255,255);  // Blanco
        $this->Cell(0,15,'Reporte DATASENA',0,1,'C',true);
        $this->Ln(5);
    }

    // -------------------------------
    // Pie de página
    // -------------------------------
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }

    // -------------------------------
    // Función NbLines
    // Calcula cuántas líneas ocupará un texto en un ancho dado
    // -------------------------------
    function NbLines($w, $txt) {
        $cw = $this->CurrentFont['cw']; // Ancho de cada carácter
        if($w==0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2*$this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n") $nb--;
        $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
        while($i<$nb) {
            $c = $s[$i];
            if($c=="\n") { $i++; $sep=-1; $j=$i; $l=0; $nl++; continue; }
            if($c==' ') $sep=$i;
            $l += $cw[$c];
            if($l>$wmax) {
                if($sep==-1) {
                    if($i==$j) $i++;
                } else $i=$sep+1;
                $sep=-1; $j=$i; $l=0; $nl++;
            } else $i++;
        }
        return $nl;
    }
}

// -------------------------------
// Crear PDF
// -------------------------------
$pdf = new PDF();
$pdf->AliasNbPages(); // Permite {nb} en Footer
$pdf->AddPage();
$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0);

// -------------------------------
// Cabecera de la tabla
// -------------------------------
$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(52, 152, 219); // Azul
$pdf->SetTextColor(255,255,255);  // Blanco
$pdf->Cell(60,10,'Campo',1,0,'C',true);
$pdf->Cell(130,10,'Valor',1,1,'C',true);

$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0);

// -------------------------------
// Fila: Tipo de reporte
// -------------------------------
$pdf->Cell(60,10,'Tipo de reporte',1,0,'L');
$pdf->Cell(130,10,utf8_decode($reporte['tipo_reporte']),1,1,'L');

// -------------------------------
// Fila: ID Referenciado
// -------------------------------
$pdf->Cell(60,10,'ID Referenciado',1,0,'L');
$pdf->Cell(130,10,$reporte['id_referenciado'],1,1,'L');

// -------------------------------
// Fila: Observación (varias líneas)
// -------------------------------
$cellHeight = 10; // Altura base de cada línea
$obs = utf8_decode($reporte['observacion']); // Texto
$nb = $pdf->NbLines(130, $obs); // Número de líneas necesarias
$h = $cellHeight * $nb;        // Altura total de la celda

$pdf->Cell(60, $h, 'Observacion', 1, 0, 'L'); // Campo

$x = $pdf->GetX(); // Guardar posición X
$y = $pdf->GetY(); // Guardar posición Y
$pdf->MultiCell(130, $cellHeight, $obs, 1, 'L'); // Valor

// Ajustar posición para la siguiente fila usando margen conocido
$pdf->SetXY(10, $y + $h); // 10 mm coincide con margen izquierdo definido

// -------------------------------
// Fila: Fecha
// -------------------------------
$pdf->Cell(60,10,'Fecha',1,0,'L'); // Campo
$pdf->Cell(130,10,$reporte['fecha_reporte'],1,1,'L'); // Valor

// -------------------------------
// Generar PDF
// -------------------------------
$pdf->Output();
