<?php
session_start();
include("includes/config.php");

if(isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $getfacultyId = $dbh->prepare("SELECT id FROM facultydata WHERE FacultyName = :facultyname AND contact = :contact");
    $getfacultyId->bindParam(':facultyname', $username, PDO::PARAM_STR);
    $getfacultyId->bindParam(':contact', $password, PDO::PARAM_INT);
    $getfacultyId->execute();
    $facultyIdRow = $getfacultyId->fetch(PDO::FETCH_ASSOC);
    $facultyid = $facultyIdRow['id'];

    $checkSql = "SELECT * FROM facultydata WHERE FacultyName = :facultyname AND contact = :contact";
    $checkQuery = $dbh->prepare($checkSql);
    $checkQuery->bindParam(':facultyname', $username, PDO::PARAM_STR);
    $checkQuery->bindParam(':contact', $password, PDO::PARAM_INT);
    $checkQuery->execute();
    $row = $checkQuery->fetch(PDO::FETCH_ASSOC);
    if($row) {
        $facultyid = $row['id'];
        $_SESSION['id'] = $facultyid;
        header("Location: dashboardfaculty.php");
        exit;
    } else {
        echo "<p>Invalid username or password.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Faculty</title>
    <link rel="stylesheet" href="style/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="wrapper">
        <form action="" method="post">
            <h1>Faculty Login</h1>
            <div class="input-box">
                <input type="text" name="username" placeholder="UserName" required> 
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt' ></i>
            </div>
            <button type="submit" name="submit" class="btn">Login</button>
            <div class="register-link">
                <p>Other Logins?
                    <a href="login.php">Click Here</a>
                </p>
            </div>
        </form>
    </div>
</body>
</html>
