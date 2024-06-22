
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
s.SubjectName,
c.ClassName,
c.ClassNameNumeric,
c.Section,
COUNT(DISTINCT rd.StudentId) AS TotalStudents,
(COUNT(CASE WHEN rd.Grades > 0 THEN 1 END) / COUNT(DISTINCT rd.StudentId)) * 100 AS PassPercentage
FROM
facultydata fd
JOIN
facultycombinationdata fcd ON fd.id = fcd.FacultyId
JOIN
classdata c ON fcd.ClassId = c.id
JOIN
subjectdata s ON fcd.SubjectId = s.id
JOIN
resultdata rd ON fcd.SubjectId = rd.SubjectId
             AND fcd.ClassId = rd.ClassId
WHERE
fd.FacultyName =:facultyname
GROUP BY
s.SubjectName,
c.ClassName,
c.ClassNameNumeric,
c.Section;";

$query = $dbh->prepare($sql);
$query->bindParam(':facultyname',$facultyname,PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);
$cnt = 1;
$chartData = [];
if ($query->rowCount() > 0) {
    foreach ($results as $result) {
        $passpercentage = $result->PassPercentage;
        $chartData[] = [
            'subjectname' =>$result->SubjectName,
            'passpercentage' =>round($passpercentage,2)
        ];
    }}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Subject Wise</title>
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
            <?php include('includes/leftbarfaculty.php'); ?>

            <div class="main-page">
                <div class="container-fluid">
                    <!-- Your page title and breadcrumb code -->

                    <section class="section">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <h5>View Subject Wise Info</h5>
                                            </div>
                                        </div>
                                        <?php if ($msg) { ?>
                                            <div class="alert alert-success left-icon-alert" role="alert">
                                                <strong>Well done!</strong><?php echo htmlentities($msg); ?>
                                            </div>
                                        <?php } else if ($error) { ?>
                                            <div class="alert alert-danger left-icon-alert" role="alert">
                                                <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                            </div>
                                        <?php } ?>
                                        <div class="panel-body p-20">
                                            <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Subject Name</th>
                                                        <th>Department Name</th>
                                                        <th>Year Name</th>
                                                        <th>Pass Percentage</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php
$sql = "SELECT
s.SubjectName,
c.ClassName,
c.ClassNameNumeric,
c.Section,
COUNT(DISTINCT rd.StudentId) AS TotalStudents,
(COUNT(CASE WHEN rd.Grades > 0 THEN 1 END) / COUNT(DISTINCT rd.StudentId)) * 100 AS PassPercentage
FROM
facultydata fd
JOIN
facultycombinationdata fcd ON fd.id = fcd.FacultyId
JOIN
classdata c ON fcd.ClassId = c.id
JOIN
subjectdata s ON fcd.SubjectId = s.id
JOIN
resultdata rd ON fcd.SubjectId = rd.SubjectId
             AND fcd.ClassId = rd.ClassId
WHERE
fd.FacultyName =:facultyname
GROUP BY
s.SubjectName,
c.ClassName,
c.ClassNameNumeric,
c.Section;";

$query = $dbh->prepare($sql);
$query->bindParam(':facultyname',$facultyname,PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);
$cnt = 1;
if ($query->rowCount() > 0) {
    foreach ($results as $result) {
        ?>
        <tr>
            <td><?php echo htmlentities($cnt); ?></td>
            <td><?php echo htmlentities($result->SubjectName); ?></td>
            <td><?php echo htmlentities($result->ClassName); ?></td>
            <td><?php echo htmlentities($result->ClassNameNumeric); ?></td>
            <td><?php echo round($result->PassPercentage,2); ?>%</td>
        </tr>
        <?php $cnt = $cnt + 1;
    }
} ?>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div id="chartdiv" style="width: 100%; height: 500px;"></div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
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
        // Themes begin
        am4core.useTheme(am4themes_animated);
        // Themes end

        // Create chart instance
        var chart = am4core.create("chartdiv", am4charts.XYChart);

        // Add data
        chart.data = <?php echo json_encode($chartData); ?>;

        // Create axes
        var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
        categoryAxis.dataFields.category = "subjectname";
        categoryAxis.title.text = "Subject Name";

        var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
        valueAxis.title.text = "Pass Percentage";

        // Create series
        var series = chart.series.push(new am4charts.ColumnSeries());
        series.dataFields.valueY = "passpercentage";
        series.dataFields.categoryX = "subjectname";
        series.name = "Pass Percentage";
        series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}%[/]";
        series.columns.template.fillOpacity = 0.8;

        var columnTemplate = series.columns.template;
        columnTemplate.strokeWidth = 2;
        columnTemplate.strokeOpacity = 1;

        // Add export menu
        chart.exporting.menu = new am4core.ExportMenu();
    });
</script>
    </body>
</html>
<?php } ?>

