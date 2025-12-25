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
if(isset($_POST['submit']))
{
$subjectname=$_POST['subjectname'];
$subjectcode=$_POST['subjectcode']; 
$credits=$_POST['credits'];
$semester=$_POST['semester'];
$sql="INSERT INTO  subjectdata(SubjectName,SubjectCode,credit,semester) VALUES(:subjectname,:subjectcode,:credits,:semester)";
$query = $dbh->prepare($sql);
$query->bindParam(':subjectname',$subjectname,PDO::PARAM_STR);
$query->bindParam(':subjectcode',$subjectcode,PDO::PARAM_STR);
$query->bindParam(':credits',$credits,PDO::PARAM_INT);
$query->bindParam(':semester',$semester,PDO::PARAM_INT);
$query->execute();
$count = $query->rowCount();
if($count>0)
{
$msg="Subject Created successfully";
}
else 
{
$error="Something went wrong. Please try again";
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
            $subjectname = $row[0];
            $subjectcode = $row[1];
            $credits = $row[2];
            $semester = $row[3];
            $checkSql = "SELECT * FROM subjectdata WHERE SubjectName = :subjectname AND SubjectCode = :subjectcode AND credit = :credit AND semester = :semester";
            $checkQuery = $dbh->prepare($checkSql);
            $checkQuery->bindParam(':subjectname', $subjectname, PDO::PARAM_STR);
            $checkQuery->bindParam(':subjectcode', $subjectcode, PDO::PARAM_STR);
            $checkQuery->bindParam(':credit',$credits,PDO::PARAM_INT);
            $checkQuery->bindParam(':semester',$semester,PDO::PARAM_INT);
            $checkQuery->execute();
    
            $rowCount = $checkQuery->rowCount();
    
            if($rowCount > 0) {
                // Record exists, update it
                $updateSql = "UPDATE subjectdata SET SubjectName = :subjectname, SubjectCode = :subjectcode,credit =:credit,semester =:semester WHERE SubjectCode = :subjectcode";
                $updateQuery = $dbh->prepare($updateSql);
                $updateQuery->bindParam(':subjectname', $subjectname, PDO::PARAM_STR);
                $updateQuery->bindParam(':subjectcode', $subjectcode, PDO::PARAM_STR);
                $updateQuery_>bindParam(':credit',$credit,PDO::PARAM_INT);
                $updateQuery->bindParam(':semester',$semester,PDO::PARAM_INT);
                $updateQuery->execute();
            } else {
        $sql = "INSERT INTO subjectdata (SubjectName, SubjectCode,credit,semester) VALUES (:subjectname, :subjectcode,:credit,:semester)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':subjectname', $subjectname, PDO::PARAM_STR);
        $query->bindParam(':subjectcode', $subjectcode, PDO::PARAM_STR);
        $query->bindParam(':credit', $credits, PDO::PARAM_INT);
        $query->bindParam(':semester',$semester,PDO::PARAM_INT);

        // Execute the query and handle errors
        $query->execute();
        $count = $query->rowCount();
        if ($count > 0) {
            $msg = "Subject Imported successfully";
        } else {
            $error = "Error importing subject. Please check your data.";
            // Additional error handling/logging if needed
        }
    }
}
} else
{
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
        <title>Subject Creation </title>
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
                                    <h2 class="title">Subject Creation</h2>
                                
                                </div>
                                
                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li> Subjects</li>
                                        <li class="active">Create Subject</li>
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
                                                    <h5>Create Subject</h5>
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
                                                        <label for="default" class="col-sm-2 control-label">Subject Name</label>
                                                        <div class="col-sm-10">
 <input type="text" name="subjectname" class="form-control" id="default" placeholder="Subject Name" required="required">
                                                        </div>
                                                    </div>
<div class="form-group">
                                                        <label for="default" class="col-sm-2 control-label">Subject Code</label>
                                                        <div class="col-sm-10">
 <input type="text" name="subjectcode" class="form-control" id="default" placeholder="Subject Code" required="required">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="default" class="col-sm-2 control-label">Credit</label>
                                                        <div class="col-sm-10">
 <input type="number" name="credits" class="form-control" id="default" placeholder="Credits" required="required">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="default" class="col-sm-2 control-label">semester</label>
                                                        <div class="col-sm-10">
 <input type="number" name="semester" class="form-control" id="default" placeholder="Credits" required="required">
                                                        </div>
                                                    </div>

                                                    
                                                    <div class="form-group">
                                                        <div class="col-sm-offset-2 col-sm-10">
                                                            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
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
<?PHP } ?>
