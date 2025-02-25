<?php
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\Fpdf;
require_once('vendor/autoload.php');
require_once('server/connect.php');

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
// Get the original filename without extension
$originalFilename = pathinfo($_GET["name"], PATHINFO_FILENAME);
// Check if user is a leaker
$isLeaker = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Get file_id from the filename
    $filename = $_GET["name"];
    $sql = "SELECT id FROM data_files WHERE file_name = '$filename'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $file_id = $row['id'];
        
        // Check if user is marked as a leaker for this file
        $leakerQuery = "SELECT * FROM leakers WHERE user_id='$user_id' AND file_id='$file_id'";
        $leakerResult = mysqli_query($conn, $leakerQuery);
        
        if ($leakerResult && mysqli_num_rows($leakerResult) > 0) {
            $isLeaker = true;
        }
    }
}

// Set the appropriate filename based on leaker status
if ($isLeaker) {
    $outputFilename = $originalFilename . "_fake_watermarked.pdf";
} else {
    $outputFilename = $originalFilename . "_watermarked.pdf";
}

// $watermarkedFilename = $originalFilename . "_watermarked.pdf";

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $outputFilename . '"');
header('Cache-Control: private, must-revalidate, max-age=0');
header('Pragma: public');

// Output the file as a forced download
$pdf->Output('D', $outputFilename);
exit();
?>