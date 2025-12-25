<?php
session_start();
error_reporting(0);
include('includes/config.php');
require 'excelReader/excel_reader2.php';
require 'excelReader/SpreadsheetReader.php';

if(strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    if(isset($_POST['submit'])) {
        $marks = $_POST['marks'];
        $class = $_POST['class'];
        $studentid = $_POST['studentid'];

        $stmt = $dbh->prepare("SELECT subjectdata.SubjectName, subjectdata.id FROM subjectcombinationdata JOIN subjectdata ON subjectdata.id = subjectcombinationdata.SubjectId WHERE subjectcombinationdata.ClassId = :cid ORDER BY subjectdata.SubjectName");
        $stmt->execute(array(':cid' => $class));
        $sid1 = array();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($sid1, $row['id']);
        }

        for($i = 0; $i < count($marks); $i++) {
            $mark = $marks[$i];
            $sid = $sid1[$i];
            
            $grades = convertGradeToMarks($mark); // Convert grade to marks

            $sql = "INSERT INTO resultdata (StudentId, ClassId, SubjectId, Grades, Marks) VALUES (:studentid, :class, :sid, :grade, :marks)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':studentid', $studentid, PDO::PARAM_STR);
            $query->bindParam(':class', $class, PDO::PARAM_STR);
            $query->bindParam(':sid', $sid, PDO::PARAM_STR);
            $query->bindParam(':grade', $grades, PDO::PARAM_INT);
            $query->bindParam(':marks', $mark, PDO::PARAM_STR);
            $query->execute();

            $lastInsertId = $dbh->lastInsertId();
            if($lastInsertId) {
                $msg = "Result info added successfully";
            } else {
                $error = "Something went wrong. Please try again";
            }
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
            $headerRow = $reader->current();
            foreach(array_slice($headerRow,1) as $subjectcode){
            if(!empty($subjectcode))
            {
                $checkSubject = $dbh->prepare("SELECT id FROM subjectdata WHERE SubjectCode = :subjectcode");
                $checkSubject->bindParam(':subjectcode', $subjectcode, PDO::PARAM_STR);
                $checkSubject->execute();
                $subjectIdRow = $checkSubject->fetch(PDO::FETCH_ASSOC);
    
                if (!$subjectIdRow) {
                    // Subject code doesn't exist, insert it into subjectdata table
                    $insertSubject = $dbh->prepare("INSERT INTO subjectdata (SubjectCode) VALUES (:subjectcode)");
                    $insertSubject->bindParam(':subjectcode', $subjectcode, PDO::PARAM_STR);
                    $insertSubject->execute();
                }
            }
        }
        foreach($reader as $key =>$row)
        {
            $rollno = $row[0];
            $getstudentId = $dbh->prepare("SELECT StudentId FROM studentdata WHERE RollId = :rollid");
            $getstudentId->bindParam(':rollid', $rollno, PDO::PARAM_STR);
            $getstudentId->execute();
            $studentIdRow = $getstudentId->fetch(PDO::FETCH_ASSOC);
            $rollid = $studentIdRow['StudentId'];
            $getclassId = $dbh->prepare("SELECT ClassId FROM studentdata WHERE RollId = :rollid");
            $getclassId->bindParam(':rollid', $rollno, PDO::PARAM_STR);
            $getclassId->execute();
            $classIdRow = $getclassId->fetch(PDO::FETCH_ASSOC);
            $classid = $classIdRow['ClassId'];
    
            foreach(array_slice($row,1) as $index =>$grade)
            {
                if(!empty($grade))
                {
                    $mark = convertGradeToMarks($grade);
                    $subjectcode = $headerRow[$index + 1]; // Adjust index for subject codes starting from second column
                        $getsubjectid = $dbh->prepare("SELECT id FROM subjectdata WHERE SubjectCode = :subjectcode");
                        $getsubjectid->bindParam(':subjectcode', $subjectcode, PDO::PARAM_STR);
                        $getsubjectid->execute();
                        $subjectidrow = $getsubjectid->fetch(PDO::FETCH_ASSOC);
                        $subjectid = $subjectidrow['id'];
                        $insertResult = $dbh->prepare("INSERT INTO resultdata (StudentId, ClassId, SubjectId, marks,Grades) VALUES (:studentid, :classid, :subjectid,:grade,:marks)");
                        $insertResult->bindParam(':studentid', $rollid, PDO::PARAM_INT);
                        $insertResult->bindParam(':classid', $classid, PDO::PARAM_INT);
                        $insertResult->bindParam(':subjectid', $subjectid, PDO::PARAM_INT);
                        $insertResult->bindParam(':grade', $grade, PDO::PARAM_STR);
                        $insertResult->bindParam(':marks',$mark,PDO::PARAM_INT);
                        $insertResult->execute();
                }
                else
                {
                    break;
                }
            }
        }
            $msg = "Combination Imported successfully";
        } else {
            $error = "Invalid file format. Please upload an Excel file (.xls or .xlsx)";
        }
    }
    
}

function convertGradeToMarks($mark) {
    // Define your conversion rules here
    switch($mark) {
        case 'O':
            return 10;
        case 'A+':
            return 9;
        case 'A':
            return 8;
        case 'B+':
            return 7;
        case 'B':
            return 6;
        // Add more cases for other grades as needed
        default:
            return 0; // Default to 0 if grade not found
    }
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SMS Admin| Add Result </title>
        <link rel="stylesheet" href="css/bootstrap.min.css" media="screen" >
        <link rel="stylesheet" href="css/font-awesome.min.css" media="screen" >
        <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen" >
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen" >
        <link rel="stylesheet" href="css/prism/prism.css" media="screen" >
        <link rel="stylesheet" href="css/select2/select2.min.css" >
        <link rel="stylesheet" href="css/main.css" media="screen" >
        <script src="js/modernizr/modernizr.min.js"></script>
        <script>
function getStudent(val) {
    $.ajax({
    type: "POST",
    url: "get_student.php",
    data:'classid='+val,
    success: function(data){
        $("#studentid").html(data);
        
    }
    });
$.ajax({
        type: "POST",
        url: "get_student.php",
        data:'classid1='+val,
        success: function(data){
            $("#subject").html(data);
            
        }
        });
}
    </script>
<script>

function getresult(val,clid) 
{   
    
var clid=$(".clid").val();
var val=$(".stid").val();;
var abh=clid+'$'+val;
//alert(abh);
    $.ajax({
        type: "POST",
        url: "get_student.php",
        data:'studclass='+abh,
        success: function(data){
            $("#reslt").html(data);
            
        }
        });
}
</script>


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
                                    <h2 class="title">Declare Result</h2>
                                
                                </div>
                                
                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                
                                        <li class="active">Student Result</li>
                                    </ul>
                                </div>
                             
                            </div>
                            <!-- /.row -->
                        </div>
                        <div class="container-fluid">
                           
                        <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel">
                                           
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
 <select name="class" class="form-control clid" id="classid" onChange="getStudent(this.value);" required="required">
<option value="">Select Class</option>
<?php $sql = "SELECT * from classdata";
$query = $dbh->prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
if($query->rowCount() > 0)
{
foreach($results as $result)
{   ?>
<option value="<?php echo htmlentities($result->id); ?>"><?php echo htmlentities($result->ClassName); ?>&nbsp;<?php echo htmlentities($result->ClassNameNumeric); ?>&nbsp; Section-<?php echo htmlentities($result->Section); ?></option>
<?php }} ?>
 </select>
                                                        </div>
                                                    </div>
<div class="form-group">
                                                        <label for="date" class="col-sm-2 control-label ">Student Name</label>
                                                        <div class="col-sm-10">
                                                    <select name="studentid" class="form-control stid" id="studentid" required="required" onChange="getresult(this.value);">
                                                    </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                      
                                                        <div class="col-sm-10">
                                                    <div  id="reslt">
                                                    </div>
                                                        </div>
                                                    </div>
                                                    
<div class="form-group">
                                                        <label for="date" class="col-sm-2 control-label">Subjects</label>
                                                        <div class="col-sm-10">
                                                    <div  id="subject">
                                                    </div>
                                                        </div>
                                                    </div>


                                                    
                                                    <div class="form-group">
                                                        <div class="col-sm-offset-2 col-sm-10">
                                                            <button type="submit" name="submit" id="submit" class="btn btn-primary">Declare Result</button>
                                                        </div>
                                                    </div>
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
