<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: login.php");
} else {
    if (isset($_GET['stid'])) {
        $classId = intval($_GET['stid']);
        $sql = "DELETE FROM studentdata WHERE StudentId = :stid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':stid', $classId, PDO::PARAM_INT);
        $query->execute();
        if ($query->rowCount()>0) {
            $msg = "Studentt deleted successfully";
        } else {
            $error = "Failed to delete class";
        }
    } else {
        $error = "Student ID not provided";
    }

    // Redirect back to the manage classes page with the appropriate message
    header("Location: manage-students.php?msg=" . urlencode($msg) . "&error=" . urlencode($error));
    exit();
}
?>
