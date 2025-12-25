
<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])=="")
    {   
    header("Location: login.php"); 
    }
    else{
        $sql = "SELECT fc.facultyid, 
    f.FacultyName, 
    c.ClassName,
    c.ClassNameNumeric,
    COUNT(CASE WHEN r.Grades > 0 THEN r.studentid END) AS total_passed_students,
    (COUNT(CASE WHEN r.Grades > 0 THEN r.studentid END) / COUNT(r.studentid) * 100) AS pass_percentage
    FROM resultdata r
    INNER JOIN facultycombinationdata fc ON r.subjectid = fc.subjectid AND r.classid = fc.classid
    INNER JOIN facultydata f ON fc.facultyid = f.id
    INNER JOIN classdata c ON r.classid = c.id
    GROUP BY fc.facultyid, f.FacultyName, c.ClassName, c.ClassNameNumeric";
    
    $query = $dbh->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    $chartData = [];
    if ($query->rowCount() > 0) {
        foreach ($results as $result) {
            $chartData[] = [
                'FacultyName' => $result->FacultyName,
                'PassPercentage' => round($result->pass_percentage, 2)
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
        <title>Faculty Wise</title>
        <link rel="stylesheet" href="css/bootstrap.min.css" media="screen" >
        <link rel="stylesheet" href="css/font-awesome.min.css" media="screen" >
        <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen" >
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen" >
        <link rel="stylesheet" href="css/prism/prism.css" media="screen" > <!-- USED FOR DEMO HELP - YOU CAN REMOVE IT -->
        <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>
        <link rel="stylesheet" href="css/main.css" media="screen" >
        <script src="js/modernizr/modernizr.min.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
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
   <?php include('includes/topbardept.php');?> 
            <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
        <div class="content-container">
            <?php include('includes/leftbardept.php'); ?>

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
                                                <h5>View Faculty Wise Info</h5>
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
        <th>Faculty Name</th>
        <th>Department Name</th>
        <th>Year Name</th>
        <th>Pass Percentage</th>
    </tr>
</thead>
<tbody>
    <?php
    $sql = "SELECT fc.facultyid, 
    f.FacultyName, 
    c.ClassName,
    c.ClassNameNumeric,
    COUNT(CASE WHEN r.Grades > 0 THEN r.studentid END) AS total_passed_students,
    (COUNT(CASE WHEN r.Grades > 0 THEN r.studentid END) / COUNT(r.studentid) * 100) AS pass_percentage
FROM resultdata r
INNER JOIN facultycombinationdata fc ON r.subjectid = fc.subjectid AND r.classid = fc.classid
INNER JOIN facultydata f ON fc.facultyid = f.id
INNER JOIN classdata c ON r.classid = c.id
GROUP BY fc.facultyid, f.FacultyName, c.ClassName, c.ClassNameNumeric;";
    $query = $dbh->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    $cnt = 1;
    if ($query->rowCount() > 0) {
        foreach ($results as $result) {
            ?>
            <tr>
                <td><?php echo htmlentities($cnt); ?></td>
                <td><?php echo htmlentities($result->FacultyName); ?></td>
                <td><?php echo htmlentities($result->ClassName); ?></td>
                <td><?php echo htmlentities($result->ClassNameNumeric); ?></td>
                <td><?php echo round($result->pass_percentage, 2); ?>%</td>
            </tr>
            <?php $cnt = $cnt + 1;
        }
    } ?>
</tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                            <div class="col-md-12">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <h5>View Faculty Info</h5>
                                        </div>
                                    </div>
                                    <div class="panel-body p-20">
                                        <div id="chartdiv" style="width: 100%; height: 500px;"></div>
                                    </div>
                                </div>
                            </div>
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
        document.addEventListener("DOMContentLoaded",function()
    {
        var chartData = <?php echo json_encode($chartData); ?>;
        am5.ready(function()
    {
        var root = am5.Root.new("chartdiv");

        root.setThemes([
            am5themes_Animated.new(root)
        ]);

        var chart = root.container.children.push(am5xy.XYChart.new(root,{
            panX: true,
            panY: true,
            wheelX: "panX",
            wheelY: "zoomX",    
            pinchZoomX: true
        }));

        var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root,{
            maxDeviation: 0.3,
            categoryField: "FacultyName",
            renderer: am5xy.AxisRendererX.new(root,{
                minGridDistance:30
            }),
            tooltip: am5.Tooltip.new(root,{})
        }));

        xAxis.data.setAll(chartData);

        var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root,{
            renderer:am5xy.AxisRendererY.new(root,{})
        }));

        var series = chart.series.push(am5xy.LineSeries.new(root,{
            name: "Pass Percentage",
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: "PassPercentage",
            categoryXField: "FacultyName",
            tooltip:am5.Tooltip.new(root,{
                labelText:"{valueY}%"
            })
        }));

        series.data.setAll(chartData);
         
        chart.set("cursor",am5xy.XYCursor.new(root,{
            behavior:"zoomX"
        }));

        chart.set("scrollbarX",am5.Scrollbar.new(root,{
            orientation:"horizontal"
        }));

        var legend = chart.children.push(am5.Legend.new(root,{}));
        legend.data.setAll(chart.series.values);
    });
    });
    </script>
    </body>
</html>
<?php } ?>

