<?php
require('fpdf/fpdf.php');
$pdf = new FPDF('l', 'mm', 'A5');

$pdf->AddPage();

$pdf->SetFont('Helvetica', 'B', 16);

$pdf->Cell(190, 7, 'INSTITUT TEKNOLOGI SEPULUH NOPEMBER', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 7, 'DAFTAR MAHASISWA KELAS PWEB B', 0, 1, 'C');

$pdf->Cell(10, 7, '', 0, 1);

$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(27, 6, 'NRP', 1, 0);
$pdf->Cell(85, 6, 'NAMA MAHASISWA', 1, 0);
$pdf->Cell(27, 6, 'TELP', 1, 0);
$pdf->Cell(50, 6, 'TANGGAL LAHIR', 1, 1);

$pdf->SetFont('Helvetica', '', 10);

include 'config.php';
$mahasiswa = mysqli_query($db_connect, "SELECT * FROM mahasiswa");

while ($row = mysqli_fetch_array($mahasiswa)) {
  $pdf->Cell(27, 6, $row['nrp'], 1, 0);
  $pdf->Cell(85, 6, $row['nama'], 1, 0);
  $pdf->Cell(27, 6, $row['telp'], 1, 0);
  $pdf->Cell(50, 6, $row['tanggal_lahir'], 1, 1);
}

$pdf->Output();
