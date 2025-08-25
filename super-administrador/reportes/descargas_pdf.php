<?php
require('fpdf/fpdf.php');

class PDF extends FPDF
{
    function Header()
    {
        // Marca de agua (logo de SENA)
        //$this->Image(__DIR__ . '/../../img/logo-sena.png', 30, 50, 150, 150);

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

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// Ejemplo de datos
$pdf->Cell(40,10,'Empresa:');
$pdf->Cell(100,10,'Mi Empresa S.A.',0,1);

$pdf->Cell(40,10,'NIT:');
$pdf->Cell(100,10,'123456789',0,1);

$pdf->Cell(40,10,'Representante:');
$pdf->Cell(100,10,'Juan Perez',0,1);

$pdf->Cell(40,10,'Observacion:');
$pdf->MultiCell(0,10,'Esta es la observacion del reporte. Aqui se detallan comentarios adicionales.',0,1);

// Ahora imprimimos la fecha justo después de Observación
$pdf->Ln(5);
$pdf->Cell(40,10,'Fecha:');
$pdf->Cell(100,10,date("d/m/Y"),0,1);

$pdf->Output();
?>
