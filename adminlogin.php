<?php
session_start();
include("includes/config.php");
if(isset($_POST['submit']))
    {
        $username=$_POST['username'];
        $password=$_POST['password'];
        $checkSql="SELECT * from admindata WHERE Username= :username AND Password = :password";
        $checkQuery = $dbh->prepare($checkSql);
        $checkQuery -> bindParam(':username',$username,PDO::PARAM_STR);
        $checkQuery -> bindParam(':password',$password,PDO::PARAM_STR);
        $checkQuery -> execute();
        $row = $checkQuery->fetch(PDO::FETCH_ASSOC);
        if($row)
        {
            $_SESSION['alogin']=$_POST['username'];
            $_SESSION['username']=$username;
            header("Location:dashboard.php");
            exit;
        }
        else
        {
            echo "<p>Invalid username or password.</p>";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="style/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

</head>
<body>
    <div class="wrapper">
        <form action="" method="post">
            <h1> Admin Login</h1>
            <div class="input-box">
                <input type="text" name="username" placeholder="UserName" required> 
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt' ></i>
            </div>
            <button type="submit" name="submit" class="btn">LOG IN</button>
            <div class="register-link">
                <p>Dont have an account?
                    <a href="adminregister.php">Register</a>
                </p>
            </div>
            <div class="register-link">
                <p>Other Logins?
                    <a href="login.php">Click Here</a>
                </p>
            </div>
        </form>
    </div>
</body>
</html>