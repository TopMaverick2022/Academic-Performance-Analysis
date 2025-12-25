
<?php
session_start();
    error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])=="")
    {   
    header("Location: login.php"); 
    }
    else{
        $facultyid = $_SESSION['id'];
        $sql = "SELECT FacultyName FROM facultydata WHERE id = :facultyid";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':facultyid', $facultyid,PDO::PARAM_INT);
    $stmt->execute();
    $facultyIdRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $facultyname = $facultyIdRow['FacultyName'];

    $sql = "SELECT 
                sd.StudentName, 
                cd.ClassNameNumeric, 
                cd.Section, 
                CASE 
                    WHEN rd.Grades > 0 THEN 100 
                    ELSE 0 
                END AS PassPercentage
            FROM 
                studentdata sd
                JOIN resultdata rd ON sd.StudentId = rd.StudentId
                JOIN classdata cd ON rd.ClassId = cd.id
                JOIN facultycombinationdata fcd ON rd.ClassId = fcd.ClassId
                JOIN facultydata fd ON fcd.FacultyId = fd.id
            WHERE 
                fd.id = (
                    SELECT id 
                    FROM facultydata 
                    WHERE FacultyName = :facultyname
                )
                AND rd.SubjectId IN (
                    SELECT SubjectId 
                    FROM facultycombinationdata 
                    WHERE FacultyId = fd.id
                      AND ClassId = cd.id
                )";
                 
    $query = $dbh->prepare($sql);
    $query->bindParam(':facultyname', $facultyname, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    $chartData = [];
    if ($query->rowCount() > 0) {
        foreach ($results as $result) {
            $chartData[] = [
                'StudentName' => $result->StudentName,
                'PassPercentage' => $result->PassPercentage
            ];
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Student Wise</title>
        <link rel="stylesheet" href="css/bootstrap.min.css" media="screen" >
        <link rel="stylesheet" href="css/font-awesome.min.css" media="screen" >
        <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen" >
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen" >
        <link rel="stylesheet" href="css/prism/prism.css" media="screen" > <!-- USED FOR DEMO HELP - YOU CAN REMOVE IT -->
        <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>
        <link rel="stylesheet" href="css/main.css" media="screen" >
        <script src="js/modernizr/modernizr.min.js"></script>
        <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
          <style>
            
        btn-secondary{--bs-btn-color:#fff;--bs-btn-bg:#6c757d;--bs-btn-border-color:#6c757d;--bs-btn-hover-color:#fff;--bs-btn-hover-bg:#5c636a;--bs-btn-hover-border-color:#565e64;--bs-btn-focus-shadow-rgb:130,138,145;--bs-btn-active-color:#fff;--bs-btn-active-bg:#565e64;--bs-btn-active-border-color:#51585e;--bs-btn-active-shadow:inset 0 3px 5px rgba(0, 0, 0, 0.125);--bs-btn-disabled-color:#fff;--bs-btn-disabled-bg:#6c757d;--bs-btn-disabled-border-color:#6c757d;}

        .errorWrap {
    padding: 10px;
    margin: 0 0 20px 0;
    background: #fff;
    border-left: 4px solid #dd3d36;
    -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
}
.succWrap{
    padding: 10px;
    margin: 0 0 20px 0;
    background: #fff;
    border-left: 4px solid #5cb85c;
    -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
}
        </style>
    </head>
    <body class="top-navbar-fixed">
        <div class="main-wrapper">

            <!-- ========== TOP NAVBAR ========== -->
   <?php include('includes/topbarfaculty.php');?> 
            <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
                <div class="content-container">
<?php include('includes/leftbarfaculty.php');?>  

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h2 class="title">Student Wise Result</h2>
                                
                                </div>
                                
                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="dashboarddept.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li> Results</li>
            							<li class="active">Classes</li>
            						</ul>
                                </div>
                             
                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->

                        <section class="section">
                            <div class="container-fluid">

                             

                                <div class="row">
                                    <div class="col-md-12">

                                        <div class="panel">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    <h5>View Classes Info</h5>
                                                </div>
                                            </div>
<?php if($msg){?>
<div class="alert alert-success left-icon-alert" role="alert">
 <strong>Well done!</strong><?php echo htmlentities($msg); ?>
 </div><?php } 
else if($error){?>
    <div class="alert alert-danger left-icon-alert" role="alert">
                                            <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                        </div>
                                        <?php } ?>
                                            <div class="panel-body p-20">

                                                <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Student Name</th>
                                                            <th>Year Name</th>
                                                            <th>Section</th>
                                                            <th>Pass Percentage</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                        $sql="SELECT 
                                        sd.StudentName, 
                                        cd.ClassNameNumeric, 
                                        cd.Section, 
                                        CONCAT(
                                            CASE 
                                                WHEN rd.Grades > 0 THEN '100%' 
                                                ELSE '0%' 
                                            END
                                        ) AS 'Grade'
                                    FROM 
                                        studentdata sd
                                        JOIN resultdata rd ON sd.StudentId = rd.StudentId
                                        JOIN classdata cd ON rd.ClassId = cd.id
                                        JOIN facultycombinationdata fcd ON rd.ClassId = fcd.ClassId
                                        JOIN facultydata fd ON fcd.FacultyId = fd.id
                                    WHERE 
                                        fd.id = (
                                            SELECT id 
                                            FROM facultydata 
                                            WHERE FacultyName =:facultyname
                                        )
                                        AND rd.SubjectId IN (
                                            SELECT SubjectId 
                                            FROM facultycombinationdata 
                                            WHERE FacultyId = fd.id
                                              AND ClassId = cd.id
                                        );
                                    ";
                                 
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':facultyname',$facultyname,PDO::PARAM_STR);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        $cnt = 1;
                                        if ($query->rowCount() > 0) {
                                            foreach ($results as $result) {
                                                //$pass_percentage = ($result->{'Passed Subjects'} / $result->{'Total Subjects'}) * 100;
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlentities($cnt); ?></td>
                                                    <td><?php echo htmlentities($result->StudentName); ?></td>
                                                    <td><?php echo htmlentities($result->ClassNameNumeric); ?></td>
                                                    <td><?php echo htmlentities($result->Section); ?></td>
                                                    <td><?php echo htmlentities($result->{'Grade'}); ?></td>
</tr>
<?php $cnt=$cnt+1;}} ?>
                                                       
                                                    
                                                    </tbody>
                                                </table>

                                                <div id="chartdiv" style="width: 100%; height: 500px;"></div>
                                                <!-- /.col-md-12 -->
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->

                                                               
                                                </div>
                                                <!-- /.col-md-12 -->
                                            </div>
                                        </div>
                                        <!-- /.panel -->
                                    </div>
                                    <!-- /.col-md-6 -->

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
        <script src="js/DataTables/datatables.min.js"></script>

        <!-- ========== THEME JS ========== -->
        <script src="js/main.js"></script>
        <script>
            $(function($) {
                $('#example').DataTable();

                $('#example2').DataTable( {
                    "scrollY":        "300px",
                    "scrollCollapse": true,
                    "paging":         false
                } );

                $('#example3').DataTable();
            });
        </script>
        <script>
        am4core.ready(function() {
            am4core.useTheme(am4themes_animated);
            var chart = am4core.create("chartdiv", am4charts.XYChart);
            chart.data = <?php echo json_encode($chartData); ?>;
            var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
            categoryAxis.dataFields.category = "StudentName";
            categoryAxis.title.text = "Student Name";

            var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
            valueAxis.title.text = "Pass Percentage";

            var series = chart.series.push(new am4charts.ColumnSeries());
            series.dataFields.valueY = "PassPercentage";
            series.dataFields.categoryX = "StudentName";
            series.name = "Pass Percentage";
            series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}%[/]";
            series.columns.template.fillOpacity = 0.8;

            var columnTemplate = series.columns.template;
            columnTemplate.strokeWidth = 2;
            columnTemplate.strokeOpacity = 1;

            chart.exporting.menu = new am4core.ExportMenu();
        });
    </script>
    </body>
</html>
<?php } ?>

