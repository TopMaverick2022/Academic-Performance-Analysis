<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: login.php");
} else {
    if (isset($_GET['classid'])) {
        $classId = intval($_GET['classid']);
        $sql = "DELETE FROM classdata WHERE id = :classid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':classid', $classId, PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount() > 0) {
            $msg = "Class deleted successfully";
        } else {
            $error = "Failed to delete class";
        }
    } else {
        $error = "Class ID not provided";
    }

    // Redirect back to the manage classes page with the appropriate message
    header("Location: manage-classes.php?msg=" . urlencode($msg) . "&error=" . urlencode($error));
    exit();
}
?>
