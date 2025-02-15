<?php
require_once("../server/connect.php");
require_once("../session.php");
require_once("../sanitize.php");
require_once("hasAccessUser.php");
require_once("library.php");

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Check if download info exists in session
if(!isset($_SESSION['download_pending']) || !isset($_SESSION['download_otp'])) {
    header('Location: list_of_received_files.php');
    exit();
}

if(isset($_POST['verify_otp'])) {
    $entered_otp = sanitize($_POST['otp']);
    $stored_otp = $_SESSION['download_otp'];
    $otp_time = $_SESSION['otp_time'];
    
    // Check if OTP is expired (2 minutes validity)
    if(time() - $otp_time > 120) {
        echo "<div class='alert alert-danger'>OTP has expired. Please request a new one.</div>";
        unset($_SESSION['download_otp']);
        unset($_SESSION['otp_time']);
        unset($_SESSION['download_pending']);
    }
    // Verify OTP
    else if($entered_otp == $stored_otp) {
        // Get file information from session
        $file_info = $_SESSION['download_pending'];
        $filename = $file_info['filename'];
        $filepath = "../assets/files/$filename";
        
        // Clear sensitive session data
        unset($_SESSION['download_otp']);
        unset($_SESSION['otp_time']);
        unset($_SESSION['download_pending']);
        
        if(file_exists($filepath)) {
            // Check if file is PDF
            $ext = pathinfo($filepath, PATHINFO_EXTENSION);
            
            if(strtolower($ext) == 'pdf') {
                // For PDF files, process through watermark script
                header("Location: ../download_with_watermark.php?name=" . $filename . "&download=true");
                exit();
            } else {
                // For non-PDF files, direct download
                header('Content-Type: application/octet-stream');
                header('Content-Description: File Transfer');
                header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filepath));
                
                ob_clean();
                flush();
                readfile($filepath);
                exit();
            }
        } else {
            echo "<div class='alert alert-danger'>File not found.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Download OTP</title>
    <?php include_once("bootstrap.php"); ?>
</head>
<body class="dashboard_background">
    <?php include_once("menubar.php"); ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-4 mx-auto">
                <div class="card my-5">
                    <div class="card-body">
                        <h4 class="text-center mb-3">Enter OTP</h4>
                        <p class="text-center">Please enter the OTP sent to your email</p>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <input type="number" 
                                       name="otp" 
                                       class="form-control" 
                                       placeholder="Enter 6-digit OTP"
                                       required>
                            </div>
                            <div class="mb-3 text-center">
                                <button type="submit" 
                                        name="verify_otp" 
                                        class="btn btn-primary">
                                    Verify & Download
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>