<?php
session_start();
if (!(isset($_SESSION['examinerusername']) && isset($_SESSION['examinercourse']))) {
    header("Location: examiner_login.php");
    exit;
}

$examinerusername=$_SESSION['examinerusername'];
$examinercourse=$_SESSION['examinercourse'];
$conn=new mysqli("localhost","root","",$examinercourse);
if($conn->connect_error)
{
    die("connection error".$conn->connect_error);
    exit;
}
$sql = "SELECT subject_name, subject_code,subject_id FROM examiner WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $examinerusername);
$stmt->execute();
$result = $stmt->get_result();
$subject_name='';
$subject_code='';
$subject_id='';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
       $subject_name=$row['subject_name'];
       $subject_code=$row['subject_code'];
       $subject_id=$row['subject_id'];
       $_SESSION['subject_id']=$subject_id;
    }
} 

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../admin/style/admin_dashboard.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar hidden" id="sidebar">
            <div class="sidebar-header">
                <h2><?php echo $examinerusername; ?></h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="my_profile.php?examinerusername=<?php echo $examinerusername?>"><i class="fas fa-user"></i>My Profile</a></li>
                <li><a href="manage_exam.php"><i class="fas fa-chart-bar"></i>Update & Modify Exams</a></li>
                <li><a href="manage_exam.php"><i class="fas fa-chart-bar"></i>Update & Modify Question</a></li>
                <li><a href="manage_exam.php"><i class="fas fa-cog"></i>Add question on exam</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
                <header class="dashboard-header">
                <h1><?php echo strtoupper(htmlspecialchars($subject_name))." (".strtoupper(htmlspecialchars($subject_code)).")";?></h1>
                <p class="subheader">Manage your Exams efficiently and perform quick actions with ease.</p>
            </header>

            <!-- Quick Actions Section -->
            <section class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-grid">
                <div class="action-card" onclick="location.href='create_exam.php'">
                    <div class="action-logo">
                        <img src="../images/examlogo.jpeg" alt="Create Student">
                    </div>
                    <h3>Create Exam</h3>
                    <p>Add new Exam to the system quickly and efficiently.</p>
                </div>

             
                 
                <div class="action-card" onclick="location.href='manage_exam.php'">
                    <div class="action-logo">
                        <img src="../images/manageexam.jpeg" alt="Manage Students">
                    </div>
                    <h3>Manage Exams</h3>
                    <p>View, update, or remove Exams information in the system.</p>
                </div>

                <div class="action-card" onclick="location.href='resultofexam.php'">
                    <div class="action-logo">
                        <img src="../images/viewresult.jpeg" alt="Manage Teachers">
                    </div>
                    <h3>View Result</h3>
                    <p>View Result of Studends</p>
                </div>
            </div>
        </section>
        <br><br><br>


         
 


        </div>

    </div>
</body>
</html>
