<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: login.php");
} else {
    if (isset($_GET['stid'])) {
        $stId = intval($_GET['stid']);
        $sql = "DELETE FROM resultdata WHERE StudentId = :stid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':stid', $stId, PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount() > 0) {
            $msg = "Result deleted successfully";
        } else {
            $error = "Failed to delete result";
        }
    } else {
        $error = "Result ID not provided";
    }

    // Redirect back to the manage classes page with the appropriate message
    header("Location: manage-result.php?msg=" . urlencode($msg) . "&error=" . urlencode($error));
    exit();
}
?>
