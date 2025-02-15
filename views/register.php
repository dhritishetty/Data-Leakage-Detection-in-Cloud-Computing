<?php require_once("../server/connect.php"); ?>
<?php include_once("../session.php"); ?>
<?php include_once("../sanitize.php"); 
function isStrongPassword($password) {
    // At least 8 characters long
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long";
    }
    
    // Check for uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one uppercase letter";
    }
    
    // Check for lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must contain at least one lowercase letter";
    }
    
    // Check for number
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number";
    }
    
    // Check for special character
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        return "Password must contain at least one special character";
    }
    
    return true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
	<?php include_once("bootstrap.php"); ?>
</head>
<body class="login_background">
	<?php include_once("menubar.php"); ?>

	<div class="container">
		<div class="row my-5">
			<div class="col-sm-4 mx-auto">
				<div class="card">
					<div class="card-body">
<?php
	$errmessage='';
	if(isset($_SESSION['error_message']))
	{
		$errmessage = $_SESSION['error_message'];
	}
	if(isset($_POST['register'])){
		$username=sanitize($_POST['username']);
		$email = '';
		// $email=sanitize($_POST['email']); commented this
		if(isset($_SESSION['register_email'])) //this
		{
			$email = $_SESSION['register_email']; //this
		} //this
		$password=sanitize($_POST['password']);
		$cpassword=sanitize($_POST['cpassword']);

		// Add password strength validation
		$passwordCheck = isStrongPassword($password);
		if($passwordCheck !== true) {
			$_SESSION['error_message'] = '<div class="alert alert-danger">' . $passwordCheck . '</div>';
			header('location:register.php');
			exit();
		}
		
		// Rest of your existing validation code...
		if($cpassword != $password){
			$_SESSION['error_message'] = '<div class="alert alert-danger">Passwords do not match</div>';
			header('location:register.php');
			exit();
		}

		$result = mysqli_query($conn,"SELECT * from users WHERE username='$username'");
		if(mysqli_num_rows($result)>0){
			$_SESSION['error_message']='<div class="alert alert-danger">Username already exists</div>';
			header('location:register.php');
			exit();
		}
		// $result = mysqli_query($conn,"SELECT * from users WHERE email='$email'"); commented this
		// if(mysqli_num_rows($result)>0){
		// 	$_SESSION['error_message']='<div class="alert alert-danger">Email already exists</div>';
		// 	header('location:register.php');
		// 	exit();
		// } commented this
		if(empty($username)){
			echo "Username is required.";
			exit();
		}
		// if(empty($email)){ commented this
		// 	echo "Email id is required.";
		// 	exit();
		// } commented this
		if(empty($password)){
			echo "Password is required.";
			exit();
		}
		if(empty($cpassword)){
			echo "Confirm password is required.";
			exit();
		}
		if($cpassword != $password){
			echo "Passwords do not match.";
			exit();
		}		
		$result=mysqli_query($conn, "INSERT INTO users(username, email, password)VALUES('$username', '$email', '$password')");
		if($result){
			unset($_SESSION['register_email']);
			unset($_SESSION['error_message']); //this
			// unset($_SESSION['forgot']); DO NOT DELETE
			$_SESSION['success_message']="Account creation successful. Please wait for admin approval.";
			header("Location:login.php");
			exit();			
		}
		else{
			echo "Something wrong, try again.";
		}
	}
 ?>
						<form action="<?=$_SERVER['PHP_SELF']?>" method='POST'>
							<div class="mb-2">
								<label for="username">Username</label>
								<input type="text" name="username" class="form-control" required autofocus>
							</div>
							<!-- <div class="mb-2"> commented this
								<label for="email">Email id</label>
								<input type="text" name="email" class="form-control" required>
							</div> commmented this -->
							<div class="mb-2">
								<label for="password">Password</label>
								<input type="password" name="password" class="form-control" required>
							</div>

							<div class="mb-2">
								<label for="cpassword">Confirm Password</label>
								<input type="password" name="cpassword" class="form-control" required>
							</div>

							<div class="mb-2 text-center">
								<input type="submit" name="register" value="Register" class="btn btn-primary">
							</div>
							
    <small class="form-text text-muted">
        Password must contain:
        <ul>
            <li>At least 8 characters</li>
            <li>One uppercase letter</li>
            <li>One lowercase letter</li>
            <li>One number</li>
            <li>One special character (!@#$%^&*(),.?":{}|<>)</li>
        </ul>
    </small>
</div>
							<?php echo $errmessage ?>

							<div class="mb-2 text-center">
								<a href="login.php" class="text-decoration-none">Already have an account?</a>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<!-- row -->
	</div>
	<script>
function validatePassword(password) {
    // Update password requirements message
    let message = '';
    if (password.length < 8) {
        message += 'Password must be at least 8 characters long<br>';
    }
    if (!/[A-Z]/.test(password)) {
        message += 'Password must contain at least one uppercase letter<br>';
    }
    if (!/[a-z]/.test(password)) {
        message += 'Password must contain at least one lowercase letter<br>';
    }
    if (!/[0-9]/.test(password)) {
        message += 'Password must contain at least one number<br>';
    }
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
        message += 'Password must contain at least one special character<br>';
    }
    return message;
}

document.querySelector('input[name="password"]').addEventListener('input', function() {
    let message = validatePassword(this.value);
    let feedbackDiv = document.getElementById('password-feedback');
    if (!feedbackDiv) {
        feedbackDiv = document.createElement('div');
        feedbackDiv.id = 'password-feedback';
        this.parentNode.appendChild(feedbackDiv);
    }
    feedbackDiv.innerHTML = message;
    feedbackDiv.style.color = message ? 'red' : 'green';
});
</script>
</body>
</html>