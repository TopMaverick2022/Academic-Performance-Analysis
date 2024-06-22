<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: login.php");
} else {
    if (isset($_GET['facultyid'])) {
        $classId = intval($_GET['facultyid']);
        $sql = "DELETE FROM facultydata WHERE id = :facultyid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':facultyid', $classId, PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount() > 0) {
            $msg = "Subject deleted successfully";
        } else {
            $error = "Failed to delete class";
        }
    } else {
        $error = "Subject ID not provided";
    }

    // Redirect back to the manage classes page with the appropriate message
    header("Location: manage-faculty.php?msg=" . urlencode($msg) . "&error=" . urlencode($error));
    exit();
}
?>
