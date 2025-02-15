<<<<<<< Updated upstream
<?php require_once("../server/connect.php"); ?>
<?php include_once("../session.php"); ?>
<?php include_once("../sanitize.php"); ?>
<?php require_once("hasAccessUser.php"); ?>
<?php require_once("library.php"); ?>
<?php require_once("create_fake_object.php"); ?>
=======
<?php
require_once("../server/connect.php");
require_once("../session.php");
require_once("../sanitize.php");
require_once("hasAccessUser.php");
require_once("library.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/PHPMailer/src/Exception.php';
require '../vendor/PHPMailer/src/PHPMailer.php';
require '../vendor/PHPMailer/src/SMTP.php';
>>>>>>> Stashed changes

function checkSecretKeyRequest($id, $user_id){
	global $conn;
	$sql="SELECT * FROM key_requests WHERE file='$id' AND request_by_user='$user_id'";
	$result=mysqli_query($conn, $sql);
	if(mysqli_num_rows($result)>0){
		$row=mysqli_fetch_array($result);
		if($row['status'] == 'pending' || $row['status']=='rejected'){
			return 'no';
		}		
		return "yes";
	}
	return 'no';
}

if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if(isset($_POST['download'])) {
    $id = $_GET['id'];    
    $user_id = $_SESSION['user_id'];
    $secret_key = sanitize($_POST['secret_key']);
    
    $sql = "SELECT * FROM data_files WHERE id='$id' AND secret_key='$secret_key'";
    $result = mysqli_query($conn, $sql);
    
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        
        // Check for leaker status
        $status = checkSecretKeyRequest($row['id'], $_SESSION['user_id']);
        if($status == "no") {
            $sql = "UPDATE users SET is_leaker='yes' WHERE id='$user_id'";
            mysqli_query($conn, $sql);
            echo "<div class='alert alert-danger'>
                You have been marked as a leaker.
            </div>";
            exit();
        }

        // Store file info in session for later use
        $_SESSION['download_pending'] = [
            'file_id' => $id,
            'filename' => $row['file_name'],
            'secret_key' => $secret_key
        ];
        
        // Get user email
        $user_sql = "SELECT email FROM users WHERE id='$user_id'";
        $user_result = mysqli_query($conn, $user_sql);
        $user_data = mysqli_fetch_array($user_result);
        $email = $user_data['email'];
        
        // Generate OTP
        $otp = rand(100000, 999999);
        $_SESSION['download_otp'] = $otp;
        $_SESSION['otp_time'] = time(); // Store OTP generation time
        
        // Send OTP via email
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'dhrithigccp@gmail.com'; // Your email
            $mail->Password = 'cypvexobbgknbdep'; // Your app password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            
            $mail->setFrom('no-reply@dld.com', 'no-reply');
            $mail->addAddress($email);
            
            $mail->isHTML(true);
            $mail->Subject = 'File Download OTP';
            $mail->Body = "Your OTP for file download is: <b>$otp</b><br>This OTP will expire in 2 minutes.<br> Don't share with anyone. <br> Regards,<br> Dhrithi, Nidhi & Sneha";
            
            if($mail->send()) {
                header('Location: verify_download_otp.php');
                exit();
            } else {
                echo "<div class='alert alert-danger'>Failed to send OTP email</div>";
            }
        } catch(Exception $e) {
            echo "<div class='alert alert-danger'>Error sending OTP: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>
            Invalid secret key.
        </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download File</title>
    <?php include_once("bootstrap.php"); ?>
</head>
<body class="dashboard_background">
<<<<<<< Updated upstream
	<?php include_once("menubar.php"); ?>
<div class="container">
	<div class="row">
		<div class="col-sm-4 mx-auto">
			<div class="card my-5">
				<div class="card-body">
<?php
if(isset($_POST['download'])){	
	$id=$_GET['id'];	
	$user_id=$_SESSION['user_id'];

	$secret_key=sanitize($_POST['secret_key']);
	$sql="SELECT * FROM data_files WHERE id='$id' AND secret_key='$secret_key'";
	$result=mysqli_query($conn, $sql);
	if($result){
		if(mysqli_num_rows($result)>0){
			$row=mysqli_fetch_array($result);


$status = checkSecretKeyRequest($row['id'], $_SESSION['user_id']);
if($status == "no"){
 // mark as leaker
	
	$subject=$row['subject'];
	$secret_key=$row['secret_key'];
	$file_id=$row['id'];
	$filename=$row['subject'];
	$fakeFileName=$filename. "_fake";
	$sql="INSERT INTO leakers(user_id, subject, file_id, secret_key)VALUES('$user_id', '$subject','$file_id','$secret_key')";
	mysqli_query($conn, $sql);
	$sql = "INSERT INTO fake_objects_log (original_file, fake_file, created_by, created_at)VALUES ('$filename', '$fakeFileName', '$user_id', NOW())";
	mysqli_query($conn, $sql);

	createFakeObject($file_id, $user_id, $filename); // Add this line

	
}


$filename = $row['file_name'];

$url="../assets/files/$filename";
$ext=pathinfo($url, PATHINFO_EXTENSION);
if($ext=="pdf"){
$url="../download_with_watermark.php?name=$filename#toolbar=0";
header("Location:$url");
exit();
}
else{
// File to download other files other than PDF
$contenttype = "application/force-download";
header("Content-Type: " . $contenttype);
header("Content-Disposition: attachment; filename=\"" .$filename. "\";");
readfile("../assets/files/".$filename);
exit();		
}

		}
		else{			
			mark_attempt($id);			
			$remaining_attempts = get_attempt($id);
			echo "
			<div class='alert alert-danger'>
				Invalid secret key, try again, Only ".$remaining_attempts+1 ." attempts left.
			</div>";
			if($remaining_attempts == "0"){
				$created_at=date("Y-m-d H:i:s");
				$sql="INSERT INTO leaked_messages(user_id, file_id, created_at)VALUES('$user_id', '$id', '$created_at')";
				$result=mysqli_query($conn, $sql);
				createFakeObject($id, $user_id, $filename); 
			}
		}
	}
}
?>
					<form action="download.php?id=<?=$_GET['id']?>" method="POST">
						<div class="mb-2">
						<input 
						placeholder="Enter 4 digit secret key to download file" 
						type="number" name="secret_key" class="form-control" required>
						</div>
						<div class="mb-2">
							<input type="submit" name="download" value="Download" class="btn btn-primary">
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
=======
    <?php include_once("menubar.php"); ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-4 mx-auto">
                <div class="card my-5">
                    <div class="card-body">
                        <h4 class="text-center mb-3">Enter Secret Key</h4>
                        <form action="<?php echo $_SERVER['PHP_SELF'] . "?id=" . $_GET['id']; ?>" method="POST">
                            <div class="mb-3">
                                <input type="number" 
                                       name="secret_key" 
                                       class="form-control" 
                                       placeholder="Enter secret key"
                                       required>
                            </div>
                            <div class="mb-3 text-center">
                                <button type="submit" 
                                        name="download" 
                                        class="btn btn-primary">
                                    Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
>>>>>>> Stashed changes
</body>
</html>