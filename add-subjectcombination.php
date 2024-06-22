<?php
session_start();
error_reporting(0);
require 'excelReader/excel_reader2.php';
require 'excelReader/SpreadsheetReader.php';
include('includes/config.php');
if(strlen($_SESSION['alogin'])=="")
    {   
    header("Location: login.php"); 
    }
    else{
        if (isset($_POST['submit'])) {
            $class = $_POST['class'];
            $status = 1;
        
            // Check if the form was submitted with semester option
            if (!empty($_POST['semester'])) {
                $semester = $_POST['semester'];
        
                // Fetch all subjects for the selected semester
                $sql_subjects = "SELECT id FROM subjectdata WHERE semester = :semester";
                $query_subjects = $dbh->prepare($sql_subjects);
                $query_subjects->bindParam(':semester', $semester, PDO::PARAM_STR);
                $query_subjects->execute();
                $subjects_rows = $query_subjects->fetchAll(PDO::FETCH_ASSOC);
        
                if ($subjects_rows) {
                    foreach ($subjects_rows as $subject_row) {
                        $subject = $subject_row['id'];
                        $sql = "INSERT INTO subjectcombinationdata (ClassId, SubjectId, status) VALUES (:class, :subject, :status)";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':class', $class, PDO::PARAM_STR);
                        $query->bindParam(':subject', $subject, PDO::PARAM_STR);
                        $query->bindParam(':status', $status, PDO::PARAM_STR);
                        $query->execute();
                        $lastInsertId = $dbh->lastInsertId();
                        if ($lastInsertId) {
                            $msg = "Combinations added successfully";
                        } else {
                            $error = "Something went wrong while adding combinations. Please try again";
                            break; // Exit the loop if there's an error
                        }
                    }
                    if (!isset($error)) {
                        header("Location: add-subjectcombination.php");
                    }
                } else {
                    $error = "No subjects found for the selected semester";
                }
            } elseif (!empty($_POST['subject'])) {
                $specific_subject = $_POST['subject'];
        
                // Insert combination for the specific subject
                $sql = "INSERT INTO subjectcombinationdata (ClassId, SubjectId, status) VALUES (:class, :specific_subject, :status)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':class', $class, PDO::PARAM_STR);
                $query->bindParam(':specific_subject', $specific_subject, PDO::PARAM_STR);
                $query->bindParam(':status', $status, PDO::PARAM_STR);
                $query->execute();
                $lastInsertId = $dbh->lastInsertId();
                if ($lastInsertId) {
                    $msg = "Combination added successfully";
                    header("Location: add-subjectcombination.php");
                } else {
                    $error = "Something went wrong. Please try again";
                }
            } else {
                $error = "Please select either a semester or a specific subject";
            }
        }
        
        
else if (isset($_POST['importExcel'])) {
    // Handle Excel file import
    $filename = $_FILES['excelFile']['name'];
    $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
    
    // Check file extension (only allow .xls and .xlsx)
    if (in_array($fileExtension, ['xls', 'xlsx'])) {
        $targetDirectory = "includes/" . $filename;
        move_uploaded_file($_FILES['excelFile']['tmp_name'], $targetDirectory);

        $reader = new SpreadsheetReader($targetDirectory);
        foreach ($reader as $key => $row) {
            $classname = $row[0];
            $yearname = $row[1];
            $section = $row[2];
            $subjectname = $row[3];
            $getClassId = $dbh->prepare("SELECT id FROM classdata WHERE ClassName = :classname AND ClassNameNumeric =:yearname AND Section =:section");
            $getClassId->bindParam(':classname', $classname, PDO::PARAM_STR);
            $getClassId->bindParam(':yearname',$yearname,PDO::PARAM_INT);
            $getClassId->bindParam('section',$section,PDO::PARAM_STR);
            $getClassId->execute();
            $classIdRow = $getClassId->fetch(PDO::FETCH_ASSOC);
            $classid = $classIdRow['id'];

            $getSubjectId = $dbh->prepare("SELECT id FROM subjectdata WHERE SubjectName = :subjectname");
            $getSubjectId->bindParam(':subjectname',$subjectname,PDO::PARAM_STR);
            $getSubjectId->execute();
            $subjectIdRow = $getSubjectId->fetch(PDO::FETCH_ASSOC);
            $subjectid = $subjectIdRow['id'];

            // Insert into subjectcombinationdata
            $insertCombination = $dbh->prepare("INSERT INTO subjectcombinationdata (ClassId, SubjectId) VALUES (:classid, :subjectid)");
            $insertCombination->bindParam(':classid', $classid, PDO::PARAM_INT);
            $insertCombination->bindParam(':subjectid', $subjectid, PDO::PARAM_INT);
            $insertCombination->execute();
        }

        $msg = "Combination Imported successfully";
    } else {
        $error = "Invalid file format. Please upload an Excel file (.xls or .xlsx)";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SMS Admin Subject Combination< </title>
        <link rel="stylesheet" href="css/bootstrap.min.css" media="screen" >
        <link rel="stylesheet" href="css/font-awesome.min.css" media="screen" >
        <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen" >
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen" >
        <link rel="stylesheet" href="css/prism/prism.css" media="screen" >
        <link rel="stylesheet" href="css/select2/select2.min.css" >
        <link rel="stylesheet" href="css/main.css" media="screen" >
        <script src="js/modernizr/modernizr.min.js"></script>
    </head>
    <body class="top-navbar-fixed">
        <div class="main-wrapper">

            <!-- ========== TOP NAVBAR ========== -->
  <?php include('includes/topbar.php');?> 
            <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
                <div class="content-container">

                    <!-- ========== LEFT SIDEBAR ========== -->
                   <?php include('includes/leftbar.php');?>  
                    <!-- /.left-sidebar -->

                    <div class="main-page">

                     <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h2 class="title">Add Subject Combination</h2>
                                
                                </div>
                                
                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li> Subjects</li>
                                        <li class="active">Add Subject Combination</li>
                                    </ul>
                                </div>
                             
                            </div>
                            <!-- /.row -->
                        </div>
                        <div class="container-fluid">
                           
                        <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    <h5>Add Subject Combination</h5>
                                                </div>
                                            </div>
                                            <div class="panel-body">
<?php if($msg){?>
<div class="alert alert-success left-icon-alert" role="alert">
 <strong>Well done!</strong><?php echo htmlentities($msg); ?>
 </div><?php } 
else if($error){?>
    <div class="alert alert-danger left-icon-alert" role="alert">
                                            <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                        </div>
                                        <?php } ?>
                                                <form class="form-horizontal" method="post">
                                                    <div class="form-group">
                                                        <label for="default" class="col-sm-2 control-label">Class</label>
                                                        <div class="col-sm-10">
 <select name="class" class="form-control" id="default" required="required">
<option value="">Select Class</option>
<?php $sql = "SELECT * from classdata";
$query = $dbh->prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
if($query->rowCount() > 0)
{
foreach($results as $result)
{   ?>
<option value="<?php echo htmlentities($result->id); ?>"><?php echo htmlentities($result->ClassName); ?>&nbsp; <?php echo htmlentities($result->ClassNameNumeric); ?>&nbsp;Section-<?php echo htmlentities($result->Section); ?></option>
<?php }} ?>
 </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
    <label for="default" class="col-sm-2 control-label">Semester</label>
    <div class="col-sm-10">
        <select name="semester" class="form-control" id="default">
            <option value="">Select Semester</option>
            <?php
            $sql = "SELECT DISTINCT semester FROM subjectdata ORDER BY semester ASC";
            $query = $dbh->prepare($sql);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_COLUMN);
            foreach ($results as $semester) {
                ?>
                <option value="<?php echo htmlentities($semester); ?>"><?php echo htmlentities($semester); ?></option>
            <?php } ?>
        </select>
    </div>
</div>

<div class="form-group">
                                                        <label for="default" class="col-sm-2 control-label">Subject</label>
                                                        <div class="col-sm-10">
 <select name="subject" class="form-control" id="default">
<option value="">Select Subject</option>
<?php $sql = "SELECT * from subjectdata";
$query = $dbh->prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
if($query->rowCount() > 0)
{
foreach($results as $result)
{   ?>
<option value="<?php echo htmlentities($result->id); ?>"><?php echo htmlentities($result->SubjectName); ?></option>
<?php }} ?>
 </select>
                                                        </div>
                                                    </div>
                                                    

                                                    
                                                    <div class="form-group">
                                                        <div class="col-sm-offset-2 col-sm-10">
                                                            <button type="submit" name="submit" class="btn btn-primary">Add</button>
                                                        </div>
                                                    </div>
                                                </form>
                                                </form>
                                                <form method="post" enctype="multipart/form-data">
    <!-- File upload field -->
    <div class="form-group has-success">
        <label for="excelFile" class="control-label">Multiple Uploads ? Click Below</label>
        <input type="file" name="excelFile" id="excelFile" class="form-control" accept=".xls,.xlsx">
        <span class="help-block">Upload an Excel file (.xls, .xlsx)</span>
    </div>
    <!-- Submit button for file upload -->
    <button type="submit" name="importExcel" class="btn btn-primary btn-labeled">Import Excel<span class="btn-label btn-label-right"><i class="fa fa-upload"></i></span></button>
</form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.col-md-12 -->
                                </div>
                    </div>
                </div>
                <!-- /.content-container -->
            </div>
            <!-- /.content-wrapper -->
        </div>
        <!-- /.main-wrapper -->
        <script src="js/jquery/jquery-2.2.4.min.js"></script>
        <script src="js/bootstrap/bootstrap.min.js"></script>
        <script src="js/pace/pace.min.js"></script>
        <script src="js/lobipanel/lobipanel.min.js"></script>
        <script src="js/iscroll/iscroll.js"></script>
        <script src="js/prism/prism.js"></script>
        <script src="js/select2/select2.min.js"></script>
        <script src="js/main.js"></script>
        <script>
            $(function($) {
                $(".js-states").select2();
                $(".js-states-limit").select2({
                    maximumSelectionLength: 2
                });
                $(".js-states-hide").select2({
                    minimumResultsForSearch: Infinity
                });
            });
        </script>
    </body>
</html>
<?PHP } ?>
