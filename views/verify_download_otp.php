<?php 
require_once("../server/connect.php");
require_once("../session.php");
require_once("../sanitize.php");
require_once("hasAccessUser.php");

// Check if download is pending
if(!isset($_SESSION['download_pending']) || !isset($_SESSION['download_otp'])) {
    header('Location: list_of_received_files.php');
    exit();
}

$error_message = '';

if(isset($_POST['verify_otp'])) {
    $entered_otp = sanitize($_POST['otp']);
    $stored_otp = $_SESSION['download_otp'];
    
    if($entered_otp == $stored_otp) {
        // OTP verified, process download
        $file_info = $_SESSION['download_pending'];
        $filename = $file_info['filename'];
        
        // Clear sensitive session data
        unset($_SESSION['download_otp']);
        unset($_SESSION['download_pending']);
        
        $url = "../assets/files/$filename";
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        
        if($ext == "pdf") {
            $url = "../download_with_watermark.php?name=$filename#toolbar=0";
            header("Location: $url");
            exit();
        } else {
            $contenttype = "application/force-download";
            header("Content-Type: " . $contenttype);
            header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
            readfile("../assets/files/" . $filename);
            exit();
        }
    } else {
        $error_message = "<div class='alert alert-danger'>Invalid OTP. Please try again.</div>";
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
                        
                        <?php if($error_message) echo $error_message; ?>
                        
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
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
                                    Verify OTP
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