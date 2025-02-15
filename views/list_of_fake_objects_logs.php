<?php require_once("../server/connect.php"); ?>
<?php include_once("../session.php"); ?>
<?php require_once("hasAccessUser.php"); ?>
<?php include_once("library.php"); ?>
<?php
// Add this after the includes
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header('location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fake Objects Log</title>
    <?php include_once("bootstrap.php"); ?>
</head>
<body class="dashboard_background">
    <?php include_once("menubar.php"); ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <table class='table table-bordered bg-white my-5'>
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Original File</th>
                            <th>Fake File</th>
                            <th>Created By</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                        $sql = "SELECT f.*, u.username, u.email 
                               FROM fake_objects_log f 
                               JOIN users u ON f.created_by = u.id 
                               ORDER BY f.created_at ASC";
                        $result = mysqli_query($conn, $sql);
                        if($result){
                            if(mysqli_num_rows($result) > 0){
                                $n = 0;
                                while($rows = mysqli_fetch_array($result)){
                                    $n++;
                    ?>
                        <tr>
                            <td><?=$n?></td>
                            <td><?=basename($rows['original_file'])?></td>
                            <td><?=basename($rows['fake_file'])?></td>
                            <td>
                                <strong>Username:</strong> <?=$rows['username']?><br/>
                                <strong>Email:</strong> <?=$rows['email']?>
                            </td>
                            <td><?=$rows['created_at']?></td>
                        </tr>
                    <?php 
                                }
                            } else {
                    ?>
                        <tr>
                            <td colspan="5" class="text-center">No fake objects created yet</td>
                        </tr>
                    <?php
                            }
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>