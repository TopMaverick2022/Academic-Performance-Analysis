<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(isset($_GET['studentid']) && is_numeric($_GET['studentid']) &&
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
        $studentname = htmlspecialchars($studentData['StudentName']);
        $rollid = htmlspecialchars($studentData['RollId']);
    } else {
        echo "<script>alert('Student Data Not Found');</script>";
        exit;
    }

    $sql = "SELECT r.marks, r.UpdationDate,r.Grades, s.SubjectName,s.SubjectCode,s.credit 
            FROM resultdata r 
            JOIN subjectdata s ON r.subjectid = s.id 
            WHERE r.studentid = :studentid AND YEAR(r.updationdate) = :year AND MONTH(r.updationdate) = :month";
    $query = $dbh->prepare($sql);
    $query->bindParam(':studentid', $studentid, PDO::PARAM_INT);
    $query->bindParam(':year', $year, PDO::PARAM_INT);
    $query->bindParam(':month', $month, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    $totalCredits = 0;
    $gradeCreditsSum = 0;
    if ($results) {
        foreach ($results as $result) {
            $gradeCreditsSum += ($result->Grades * $result->credit);
            $totalCredits += $result->credit;
        }
    }
    $gpa = ($totalCredits > 0) ? ($gradeCreditsSum / $totalCredits) : 0;
    $gpa = number_format($gpa, 2);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Result Management System</title>
        <link rel="stylesheet" href="css/bootstrap.min.css" media="screen" >
        <link rel="stylesheet" href="css/font-awesome.min.css" media="screen" >
        <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen" >
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen" >
        <link rel="stylesheet" href="css/prism/prism.css" media="screen" >
        <link rel="stylesheet" href="css/main.css" media="screen" >
        <script src="js/modernizr/modernizr.min.js"></script>
    </head>
    <body>
        <div class="main-wrapper">
            <div class="content-wrapper">
                <div class="content-container">

         
                    <!-- /.left-sidebar -->

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-12">
                                    <h2 class="title" align="center">Result Management System</h2>
                                </div>
                            </div>
                            <!-- /.row -->
                          
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->

                        <section class="section">
                            <div class="container-fluid">

                                <div class="row">
                              
                             

                                    <div class="col-md-8 col-md-offset-2">
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                <p><b>Student Name:</b> <?php echo $studentname; ?></p>
                                                <p><b>Student Roll Id:</b> <?php echo $rollid; ?></p>
                                            </div>
                                            <div class="panel-body p-20">


                                                <table class="table table-hover table-bordered">
                                                <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Subject Code</th>
                                                            <th>Subject</th>    
                                                            <th>Marks</th>
                                                        </tr>
                                               </thead> 	
                                                	<tbody>
                                                    <?php
                                                if ($results) {
                                                    $cnt = 1;
                                                    foreach ($results as $result) {
                                                        ?>
                                                        <tr>
                                                            <th scope="row"><?php echo htmlentities($cnt); ?></th>
                                                            <td><?php echo htmlentities($result->SubjectCode); ?></td>
                                                            <td><?php echo htmlentities($result->SubjectName); ?></td>
                                                            <td><?php echo htmlentities($result->marks); ?></td>
                                                            <td><?php echo htmlentities($result->updationdate); ?></td>
                                                        </tr>
                                                        <?php
                                                        $cnt++;
                                                    }} else {
                                                        echo "<tr><td colspan='4'>No results found for the given criteria.</td></tr>";
                                                    }
?>
<tr>
<tr>
    <th scope="row" colspan="3">Grade Point Average</th>
    <td><?php echo $gpa; ?></td>
</tr>
<tr>
    <th scope="row" colspan="3">Download Resulty</th>
    <td><b><a href="download-result.php?studentid=<?php echo $studentid; ?>&year=<?php echo $year; ?>&month=<?php echo $month; ?>">Download PDF</a></b></td>
</tr>

                                        </div>



                                                	</tbody>
                                                </table>

                                            </div>
                                        </div>
                                        <!-- /.panel -->
                                    </div>
                                    <!-- /.col-md-6 -->

                                    <div class="form-group">
                                                           
                                                            <div class="col-sm-6">
                                                               <a href="semester.php">Back to Home</a>
                                                            </div>
                                                        </div>

                                </div>
                                <!-- /.row -->
  
                            </div>
                            <!-- /.container-fluid -->
                        </section>
                        <!-- /.section -->

                    </div>
                    <!-- /.main-page -->

                  
                </div>
                <!-- /.content-container -->
            </div>
            <!-- /.content-wrapper -->

        </div>
        <!-- /.main-wrapper -->

        <!-- ========== COMMON JS FILES ========== -->
        <script src="js/jquery/jquery-2.2.4.min.js"></script>
        <script src="js/bootstrap/bootstrap.min.js"></script>
        <script src="js/pace/pace.min.js"></script>
        <script src="js/lobipanel/lobipanel.min.js"></script>
        <script src="js/iscroll/iscroll.js"></script>

        <!-- ========== PAGE JS FILES ========== -->
        <script src="js/prism/prism.js"></script>

        <!-- ========== THEME JS ========== -->
        <script src="js/main.js"></script>
        <script>
            $(function($) {

            });
        </script>

        <!-- ========== ADD custom.js FILE BELOW WITH YOUR CHANGES ========== -->

    </body>
</html>
