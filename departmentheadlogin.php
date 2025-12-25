<?php
session_start();
include("includes/config.php");

// Check if the form is submitted
if(isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $getfacultyId = $dbh->prepare("SELECT id FROM departmentdata WHERE Username =:username AND Password =:password");
    $getfacultyId->bindParam(':username', $username, PDO::PARAM_STR);
    $getfacultyId->bindParam(':password', $password, PDO::PARAM_STR);
    $getfacultyId->execute();
    $facultyIdRow = $getfacultyId->fetch(PDO::FETCH_ASSOC);
    $facultyid = $facultyIdRow['id'];

    $checkSql = "SELECT * FROM departmentdata WHERE Username =:username AND Password =:password";
    $checkQuery = $dbh->prepare($checkSql);
    $checkQuery->bindParam(':username', $username, PDO::PARAM_STR);
    $checkQuery->bindParam(':password', $password, PDO::PARAM_INT);
    $checkQuery->execute();
    $row = $checkQuery->fetch(PDO::FETCH_ASSOC);
    if($row) {
        // Correct username and password
        $departmentid = $row['id'];
        $_SESSION['id'] = $departmentid;
        header("Location: dashboarddept.php");
        exit;
    } else {
        // Incorrect username or password
        $error_message = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Department</title>
    <link rel="stylesheet" href="style/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="wrapper">
        <form action="" method="post">
            <h1>Department Login</h1>
            <div class="input-box">
                <input type="text" name="username" placeholder="UserName" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt' ></i>
            </div>
            <button type="submit" name="submit" class="btn">Login</button>
            <?php
            if(isset($error_message)) {
                echo "<p>$error_message</p>";
            }
            ?>
            <div class="register-link">
                <p>Other Logins?
                    <a href="login.php">Click Here</a>
                </p>
            </div>
        </form>
    </div>
</body>
</html>
