<?php
session_start();
include("includes/config.php");
error_reporting(0);
$error = '';

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    //Retrieveing Id from the database using the specific username and password
    $getstudentid = $dbh->prepare("SELECT StudentId FROM studentdata WHERE StudentName = :studentname AND RollId = :password");
    $getstudentid->bindParam(':studentname',$username,PDO::PARAM_STR);
    $getstudentid->bindParam(':password',$password,PDO::PARAM_INT);
    $getstudentid->execute();
    $studentIdRow = $getstudentid->fetch(PDO::FETCH_ASSOC);
    $studentid = $studentIdRow['StudentId'];

    //checking the data whether they are in database of the table
    $sql="SELECT * FROM studentdata WHERE StudentName = :username AND RollId = :password";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username',$username,PDO::PARAM_STR);
    $query->bindParam(':password',$password,PDO::PARAM_INT);
    $query->execute();
    $row=$query->fetch(PDO::FETCH_ASSOC);
    if($row)
    {
    $studentid = $row['StudentId'];
    $_SESSION['StudentId']=$studentid;
    header("location:dashboardstudent.php");
    }
    else
    {
        $error="Invalid Username and password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Student</title>
    <link rel="stylesheet" href="style/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="wrapper">
        <form action="" method="post">
            <h1>Student Login</h1>
            <div class="input-box">
                <input type="text" name="username" placeholder="UserName" required> 
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            <button type="submit" name="submit" class="btn">Login</button>
            <?php
            if (!empty($error)) {
            ?>
            <div class="alert alert-danger left-icon-alert" role="alert">
                <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
            </div>
            <?php
            }
            ?>
        </form>
    </div>
</body>
</html>
