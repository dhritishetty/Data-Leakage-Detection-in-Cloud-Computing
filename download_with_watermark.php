<?php
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\Fpdf;
require_once('vendor/autoload.php');

session_start();

date_default_timezone_set('Asia/Kolkata');

// Generate unique identifier (UID)
$username = $_SESSION['username'] . "-" . date('d-m-Y H:i:s');

// Step 1: Convert Identifier to Binary
function convertToBinary($identifier) {
    $binary = '';
    for ($i = 0; $i < strlen($identifier); $i++) {
        $binary .= sprintf("%08b", ord($identifier[$i]));
    }
    return $binary;
}

// Step 2: Format Binary Data
function formatBinaryData($binary) {
    // Split the binary into chunks of 8 bits (1 byte) for better readability
    $formatted = '';
    $chunkSize = 8;
    for ($i = 0; $i < strlen($binary); $i += $chunkSize) {
        $formatted .= substr($binary, $i, $chunkSize) . ' ';
    }
    return trim($formatted);
}

// Step 3: Embed Watermark into File
function addWatermark($x, $y, $watermarkText, $angle, $pdf) {
    $angle = $angle * M_PI / 180; // Convert angle to radians
    $c = cos($angle);
    $s = sin($angle);
    $cx = $x; // X coordinate
    $cy = (300 - $y); // Y coordinate (inverted for PDF coordinate system)
    
    // Save the current state
    $pdf->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
    
    // Set the angle for the text
    $pdf->Text($x, $y, $watermarkText);
    
    // Restore the state
    $pdf->_out('Q');
}

// Convert UID to binary and format it
$binaryUID = convertToBinary($username);
$formattedBinary = formatBinaryData($binaryUID);

$pdf = new Fpdi();
$fileInput = "assets/files/" . $_GET["name"];
$pages_count = $pdf->setSourceFile($fileInput);

for ($i = 1; $i <= $pages_count; $i++) {
    $pdf->AddPage();
    $tplIdx = $pdf->importPage($i);
    $pdf->useTemplate($tplIdx, 0, 0);
    $pdf->SetFont('Courier', 'B', 8); // Use Courier font for binary data
    $pdf->SetTextColor(192, 192, 192); // Set watermark text color to light gray
    $watermarkText = $formattedBinary;

    // Get page dimensions
    $pageWidth = $pdf->GetPageWidth();
    $pageHeight = $pdf->GetPageHeight();

    // Call addWatermark with a diagonal angle (e.g., 45 degrees) from bottom-left to top-right
    addWatermark(0, $pageHeight, $watermarkText, 45, $pdf); // Start from bottom-left corner
}

// Step 4: Output the file with the embedded watermark
$pdf->Output();
?>