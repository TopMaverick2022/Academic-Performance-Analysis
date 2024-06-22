<?php
include("includes/config.php");

$errors = []; // Array to store validation errors

if(isset($_POST['submit'])) {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phno = $_POST['phno'];
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirmpassword'];

    // Check if full name and username are the same
    if($fullname === $username) {
        $errors[] = "Full Name and Username should not be the same.";
    }

    // Check if password and confirm password match
    if($password !== $confirmpassword) {
        $errors[] = "Password and Confirm Password do not match.";
    }

    // Check if there are already users with the same username or phone number
    $checkSql = "SELECT * FROM admindata WHERE Username = :username OR Phno = :phno";
    $checkQuery = $dbh->prepare($checkSql);
    $checkQuery->bindParam(':username', $username, PDO::PARAM_STR);
    $checkQuery->bindParam(':phno', $phno, PDO::PARAM_STR);
    $checkQuery->execute();

    $row = $checkQuery->fetch(PDO::FETCH_ASSOC);
    if($row) {
        $errors[] = "Username or Phone Number already exists.";
    }

    // If there are no validation errors, proceed to save the user's information
    if(empty($errors)) {
        $insertSql = "INSERT INTO admindata (Fullname, Username, email, Phno, Password) 
                      VALUES (:fullname, :username, :email, :phno, :password)";
        $insertQuery = $dbh->prepare($insertSql);
        $insertQuery->bindParam(':fullname', $fullname, PDO::PARAM_STR);
        $insertQuery->bindParam(':username', $username, PDO::PARAM_STR);
        $insertQuery->bindParam(':email', $email, PDO::PARAM_STR);
        $insertQuery->bindParam(':phno', $phno, PDO::PARAM_STR);
        $insertQuery->bindParam(':password', $password, PDO::PARAM_STR);
        $insertQuery->execute();
        echo"<script>
        alert('registration successful');
        </script>";
        header("location:adminlogin.php");
        // You can redirect the user to a login page or any other page after successful registration
    } else {
        // Display validation errors
        foreach($errors as $error) {
            echo "<p>$error</p>";
        }
    }
}
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
    <link rel="stylesheet" href="style/style1.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="wrapper">
        <form action="" method="post">
            <h1>Registration</h1>
            <div class="input-box">
                <div class="input-field">
                    <input type="text"  name="fullname" placeholder="Full Name" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-field">
                    <input type="text"  name="username" placeholder="UserName" required>
                    <i class='bx bxs-user'></i>
                </div>
                </div>

                <div class="input-box">
                <div class="input-field">
                    <input type="email" name="email"placeholder="Email Id" required>
                    <i class='bx bxs-envelope'></i>
                </div>
                <div class="input-field">
                    <input type="number" name="phno" placeholder="Phno" required>
                    <i class='bx bxs-phone'></i>
                </div>
                </div>

                <div class="input-box">
                <div class="input-field">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class='bx bxs-lock-alt' ></i>
                </div>
                <div class="input-field">
                    <input type="password" name="confirmpassword" placeholder="Confirm Password" required>
                    <i class='bx bxs-lock-alt' ></i>
                </div>
            </div>

            <label>
                <input type="checkbox" required>I hereby declare that the information provided is true and correct.
            </label>

            <button type="submit" class="btn" name="submit">Register</button>
            <div class="register-link">
                <p>Already have an account?
                    <a href="adminlogin.php">Login</a>
                </p>
            </div>
        </form>
    </div>
</body>
</html>