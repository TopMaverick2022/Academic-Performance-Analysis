<?php
session_start();
include('includes/config.php');
require_once('TCPDF/tcpdf.php'); // Adjust the path to your TCPDF installation

if (isset($_GET['studentid']) && is_numeric($_GET['studentid']) &&
    isset($_GET['year']) && is_numeric($_GET['year']) &&
    isset($_GET['month']) && is_numeric($_GET['month'])) {
    $studentid = intval($_GET['studentid']);
    $year = intval($_GET['year']);
    $month = intval($_GET['month']);
} else {
    die("Invalid input.");
}

try {
    // Fetch student data
    $sql = "SELECT StudentName, RollId FROM studentdata WHERE StudentId = :studentid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':studentid', $studentid, PDO::PARAM_INT);
    $query->execute();
    $studentData = $query->fetch(PDO::FETCH_ASSOC);
    if ($studentData) {
        $studentName = htmlspecialchars($studentData['StudentName']);
        $rollId = htmlspecialchars($studentData['RollId']);
    } else {
        die("Student Data Not Found");
    }

    // Fetch result data
    $sql = "SELECT r.marks, r.updationdate, s.SubjectName,s.SubjectCode 
            FROM resultdata r 
            JOIN subjectdata s ON r.subjectid = s.id 
            WHERE r.studentid = :studentid AND YEAR(r.updationdate) = :year AND MONTH(r.updationdate) = :month";
    $query = $dbh->prepare($sql);
    $query->bindParam(':studentid', $studentid, PDO::PARAM_INT);
    $query->bindParam(':year', $year, PDO::PARAM_INT);
    $query->bindParam(':month', $month, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Create a new PDF document
$pdf = new TCPDF();

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Result Management System');
$pdf->SetTitle('Student Result');
$pdf->SetSubject('Student Result Details');
$pdf->SetKeywords('TCPDF, PDF, student, result, report');

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Add a title
$pdf->Cell(0, 10, 'Student Result', 0, 1, 'C');

// Add student details
$pdf->Cell(0, 10, 'Student Name: ' . $studentName, 0, 1);
$pdf->Cell(0, 10, 'Student Roll ID: ' . $rollId, 0, 1);

// Add a table header
$pdf->Ln(10); // Line break
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(10, 10, '#', 1);
$pdf->Cell(40,10,'SubjectCode',1);
$pdf->Cell(100, 10, 'Subject', 1);
$pdf->Cell(40, 10, 'Marks', 1);
$pdf->Ln();

// Add table rows
$pdf->SetFont('helvetica', '', 12);
if ($results) {
    $cnt = 1;
    foreach ($results as $result) {
        $pdf->Cell(10, 10, $cnt, 1);
        $pdf->Cell(40,10,$result->SubjectCode,1);
        $pdf->Cell(100, 10, $result->SubjectName, 1);
        $pdf->Cell(40, 10, $result->marks, 1);
        $pdf->Ln();
        $cnt++;
    }
} else {
    $pdf->Cell(0, 10, 'No results found for the given criteria.', 1, 1, 'C');
}

// Close and output PDF document
$pdf->Output('result.pdf', 'D');
exit();
?>