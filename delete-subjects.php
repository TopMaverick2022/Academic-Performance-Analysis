<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: login.php");
} else {
    if (isset($_GET['subjectid'])) {
        $classId = intval($_GET['subjectid']);
        $sql = "DELETE FROM subjectdata WHERE id = :subjectid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':subjectid', $classId, PDO::PARAM_INT);
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
    header("Location: manage-subjects.php?msg=" . urlencode($msg) . "&error=" . urlencode($error));
    exit();
}
?>
