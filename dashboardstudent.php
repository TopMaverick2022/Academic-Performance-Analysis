<?php
session_start();
//error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])=="")
    {   
    header("Location: login.php"); 
    }
    else{
        $studentid = $_SESSION['StudentId'];
        $sql = "SELECT StudentName, passedoutyear FROM studentdata WHERE StudentId = :studentid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':studentid', $studentid, PDO::PARAM_INT);
        $query->execute();
        $studentrow = $query->fetch(PDO::FETCH_ASSOC);
        $studentname = $studentrow['StudentName'];
        $passedoutyear = $studentrow['passedoutyear'];

        $resultSql = "SELECT PostingDate FROM resultdata WHERE StudentId = :studentid ORDER BY PostingDate DESC LIMIT 1";
        $resultQuery = $dbh->prepare($resultSql);
        $resultQuery->bindParam(':studentid', $studentid, PDO::PARAM_INT);
        $resultQuery->execute();
        $resultRow = $resultQuery->fetch(PDO::FETCH_ASSOC);
        $postingDate = $resultRow['PostingDate'];

        $currentDate = date('Y-m-d'); // Current date
        $dateDiff = date_diff(date_create($postingDate), date_create($currentDate))->format('%a');
        $semestersAppeared = ceil($dateDiff / 180);
        
        ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Student Home</title>
        <link rel="stylesheet" href="css/bootstrap.min.css" media="screen" >
        <link rel="stylesheet" href="css/font-awesome.min.css" media="screen" >
        <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen" >
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen" >
        <link rel="stylesheet" href="css/toastr/toastr.min.css" media="screen" >
        <link rel="stylesheet" href="css/icheck/skins/line/blue.css" >
        <link rel="stylesheet" href="css/icheck/skins/line/red.css" >
        <link rel="stylesheet" href="css/icheck/skins/line/green.css" >
        <link rel="stylesheet" href="css/main.css" media="screen" >
        <script src="js/modernizr/modernizr.min.js"></script>
        <script src="js/amcharts5/amcharts.js"></script>
        <script src="js/amcharts5/serial.js"></script>
        <script src="js/amcharts5/themes/dark.js"></script>
        </style>
    </head>
    <body class="top-navbar-fixed">
        <div class="main-wrapper">
              <?php include('includes/topbarstudent.php');?>
            <div class="content-wrapper">
                <div class="content-container">

                    <?php include('includes/leftbarstudent.php');?>

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-sm-6">
                                    <h2 class="title">Dashboard</h2>

                                </div>
                                <!-- /.col-sm-6 -->
                            </div>
                            <!-- /.row -->

                        </div>
                        <!-- /.container-fluid -->

                        <section class="section">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                        <a class="dashboard-stat bg-primary" href="manage-students.php">
                                            <span class="number counter"><?php echo $semestersAppeared; ?></span>
                                            <span class="name">Semesters Appeared</span>
                                            <span class="bg-icon"><i class="fa fa-users"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>
                                    <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                        <a class="dashboard-stat bg-danger" href="manage-subjects.php">
                                            <?php
                                            $resultSql = "SELECT COUNT(DISTINCT subjectid) AS subjectCount FROM resultdata WHERE StudentId = :studentid";
                                            $resultQuery = $dbh->prepare($resultSql);
                                            $resultQuery->bindParam(':studentid', $studentid, PDO::PARAM_INT);
                                            $resultQuery->execute();
                                            $resultRow = $resultQuery->fetch(PDO::FETCH_ASSOC);
                                            $subjectCount = $resultRow['subjectCount']; ?>

                                            <span class="number counter"><?php echo $subjectCount; ?></span>

                                            <span class="name">Subjects Listed</span>
                                            <span class="bg-icon"><i class="fa fa-ticket"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>
                                    <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                        <a class="dashboard-stat bg-warning" href="manage-classes.php">
                                            <?php 
                                            $cgpaSql = "SELECT SUM(Grades * credit) / SUM(credit) AS cgpa FROM resultdata 
                                            INNER JOIN subjectdata ON resultdata.subjectid = subjectdata.id 
                                            WHERE resultdata.StudentId = :studentid";
                                $cgpaQuery = $dbh->prepare($cgpaSql);
                                $cgpaQuery->bindParam(':studentid', $studentid, PDO::PARAM_INT);
                                $cgpaQuery->execute();
                                $cgpaRow = $cgpaQuery->fetch(PDO::FETCH_ASSOC);
                                $cgpa = $cgpaRow['cgpa'];
                                            ?>
                                            <span class="number counter"><?php echo $cgpa; ?></span>
                                            <span class="name">Total CGPA</span>
                                            <span class="bg-icon"><i class="fa fa-bank"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>
                                    <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                        <a class="dashboard-stat bg-success" href="manage-results.php">
                                            <?php
                                            $arrearsSql = "SELECT COUNT(*) AS arrearsCount FROM resultdata WHERE StudentId = :studentid AND Grades = 0";
                                            $arrearsQuery = $dbh->prepare($arrearsSql);
                                            $arrearsQuery->bindParam(':studentid', $studentid, PDO::PARAM_INT);
                                            $arrearsQuery->execute();
                                            $arrearsRow = $arrearsQuery->fetch(PDO::FETCH_ASSOC);
                                            $arrearsCount = $arrearsRow['arrearsCount']; ?>
                                            <span class="number counter"><?php echo $arrearsCount; ?></span>
                                            <span class="name">Arrear Subjects</span>
                                            <span class="bg-icon"><i class="fa fa-file-text"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>
                                    <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->
                                    
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
        <script src="js/jquery-ui/jquery-ui.min.js"></script>
        <script src="js/bootstrap/bootstrap.min.js"></script>
        <script src="js/pace/pace.min.js"></script>
        <script src="js/lobipanel/lobipanel.min.js"></script>
        <script src="js/iscroll/iscroll.js"></script>

        <!-- ========== PAGE JS FILES ========== -->
        <script src="js/prism/prism.js"></script>
        <script src="js/waypoint/waypoints.min.js"></script>
        <script src="js/counterUp/jquery.counterup.min.js"></script>
        <script src="js/amcharts/amcharts.js"></script>
        <script src="js/amcharts/serial.js"></script>
        <script src="js/amcharts/plugins/export/export.min.js"></script>
        <link rel="stylesheet" href="js/amcharts/plugins/export/export.css" type="text/css" media="all" />
        <script src="js/amcharts/themes/light.js"></script>
        <script src="js/toastr/toastr.min.js"></script>
        <script src="js/icheck/icheck.min.js"></script>

        <!-- ========== THEME JS ========== -->
        <script src="js/main.js"></script>
        <script src="js/production-chart.js"></script>
        <script src="js/traffic-chart.js"></script>
        <script src="js/task-list.js"></script>
        <script>
            $(function(){

                // Counter for dashboard stats
                $('.counter').counterUp({
                    delay: 10,
                    time: 1000
                });

                // Welcome notification
                toastr.options = {
                  "closeButton": true,
                  "debug": false,
                  "newestOnTop": false,
                  "progressBar": false,
                  "positionClass": "toast-top-right",
                  "preventDuplicates": false,
                  "onclick": null,
                  "showDuration": "300",
                  "hideDuration": "1000",
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showEasing": "swing",
                  "hideEasing": "linear",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
                }
                toastr["success"]( "Welcome to student Result Management System!");

            });
        </script>
        <script>
            AmCharts.makeChart("chartdiv", {
                "type": "serial",
                "theme": "light",
                "dataProvider": [{
                    "semester": "Sem 1",
                    "mark": 7.4
                }, {
                    "semester": "Sem 2",
                    "mark": 9.53
                }, {
                    "semester": "Sem 3",
                    "mark": 9.36
                }, {
                    "semester": "Sem 4",
                    "mark": 7.96
                }, {
                    "semester": "Sem 5",
                    "mark": 8.16
                }, {
                    "semester": "Sem 6",
                    "mark": 8.63
                }],
                "valueAxes": [{
                    "axisAlpha": 0,
                    "position": "left",
                    "title": "Marks"
                }],
                "startDuration": 1,
                "graphs": [{
                    "balloonText": "[[category]]: <b>[[value]]</b>",
                    "fillColorsField": "color",
                    "fillAlphas": 0.9,
                    "lineAlpha": 0.2,
                    "type": "column",
                    "valueField": "mark"
                }],
                "chartCursor": {
                    "categoryBalloonEnabled": false,
                    "cursorAlpha": 0,
                    "zoomable": false
                },
                "categoryField": "semester",
                "categoryAxis": {
                    "gridPosition": "start",
                    "labelRotation": 45
                },
                "export": {
                    "enabled": true
                }
            });
        </script>
    </body>
</html>
<?php } ?>
