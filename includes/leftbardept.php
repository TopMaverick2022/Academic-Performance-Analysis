<?php
session_start(); // Start the session
error_reporting(0);
$username ="Unknown";
if (isset($_SESSION['id'])) {
    // Include your database connection file
    // Fetch the username from the database using the user ID from the session
    $departmentid = $_SESSION['id'];
    $sql = "SELECT Username FROM departmentdata WHERE id = :departmentid";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':departmentid', $departmentid,PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $username = $user["Username"];
    } else {
        // Handle the case where the username is not found for the department ID
        $username = "Unknown";
    }
} 
?>
?><div class="left-sidebar bg-black-300 box-shadow ">
                        <div class="sidebar-content">
                            <div class="user-info closed">
                                <img src="http://placehold.it/90/c2c2c2?text=User" alt="John Doe" class="img-circle profile-img">
                                <h6 class="title"><?php echo "$username"; ?></h6>
                            </div>
                            <!-- /.user-info -->

                            <div class="sidebar-nav">
                                <ul class="side-nav color-gray">
                                    <li class="nav-header">
                                        <span class="">Main Category</span>
                                    </li>
                                    <li>
                                        <a href="dashboarddept.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span> </a>
                                     
                                    </li>

                                    <li class="nav-header">
                                        <span class="">Results</span>
                                    </li>
                                    <li class="has-children">
                                            <li><a href="studentwise.php"><i class="fa fa-bars"></i> <span>Students Wise</span></a></li>
                                            <li><a href="classwise.php"><i class="fa fa fa-server"></i> <span>Class Wise</span></a></li>
                                           
                                    </li>
  <li class="has-children">
                                        
                                            <li><a href="facultywise.php"><i class="fa fa-bars"></i> <span>Faculty Wise</span></a></li>
                                            <li><a href="subjectwise.php"><i class="fa fa fa-server"></i> <span>Subject Wise</span></a></li>
                                        </ul>
                                    </li>
                                    
                            </div>
                            <!-- /.sidebar-nav -->
                        </div>
                        <!-- /.sidebar-content -->
                    </div>