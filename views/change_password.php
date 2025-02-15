<?php require_once("../server/connect.php"); ?>
<?php include_once("../session.php"); 
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
<body class="dashboard_background">
<?php include_once("menubar.php"); ?>
    <div class="container" style="padding-top:12%">
        <div style="width:50%;margin:auto;border:1px solid black;padding:2.5%;border-radius:10px">
        <h2 class="text-center">Change password</h2><br>
<?php
$userid=-1;
// if(isset($_SESSION['register_email']))
// {
    // echo $_SESSION['register_email'];
// }
if(isset($_SESSION['user_id']))
{
   $userid=$_SESSION['user_id']; 
}
elseif(isset($_SESSION['register_email']))
{
    // echo $_SESSION['register_email'];
    $email = $_SESSION['register_email'];
    $result = mysqli_query($conn,"SELECT id FROM users WHERE email='$email'");
    $row = mysqli_fetch_array($result);
    $userid = $row['id'];
    // echo $userid;
}
if(isset($_POST['change_password'])){
    $password=$_POST['password'];
    $cpassword=$_POST['cpassword'];
    if(!empty($password) && !empty($cpassword))
    {
        // Add password strength validation
        $passwordCheck = isStrongPassword($password);
        if($passwordCheck !== true) {
            echo "<div class='alert alert-danger'><strong>Failed:</strong> " . $passwordCheck . "</div>";
            exit();
        }

        if($password ==$cpassword)
        {
            $sql="UPDATE users SET password='$password' WHERE id='$userid' LIMIT 1";
            $result=mysqli_query($conn,$sql);
            if($result)
            {
                echo "<div class='alert alert-success'><strong>Success:</strong> Successfully updated.</div>";
                unset($_SESSION['register_email']);
                // unset($_SESSION['forgot']); DO NOT DELETE
            }
        }
        else
        {
            echo "<div class='alert alert-danger'><strong>Failed:</strong> Please re-enter password correctly.</div>";
        }
    }
    else
    {
        echo "<div class='alert alert-danger'><strong>Failed:</strong> Please fill in all the fields.</div>";
    }
}
?>
<div>
<form action="<?=$_SERVER['PHP_SELF']?>" method="POST">
    <div class="mb-3">
		<label for="password">New Password:</label>
		<input type="password" name="password" class="form-control" required>
	</div>
    <div class="mb-3">
		<label for="cpassword">Confirm Password:</label>
		<input type="password" name="cpassword" class="form-control" required>
	</div>
    <br>
    <div class="mb-3 text-center">
		<button type="submit" name="change_password" class="btn btn-primary">Save</button>
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
</form>
</div>
</div>
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

<!-- style="width:70%;margin:auto" -->