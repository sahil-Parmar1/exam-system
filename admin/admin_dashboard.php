<?php
session_start();
if (!(isset($_SESSION['adminusername']) && isset($_SESSION['admincourse']))) {
    header("Location: admin_login.php");
    exit;
}
$adminusername = $_SESSION['adminusername'];
$admincourse = $_SESSION['admincourse'];
$conn=new mysqli("localhost","root","",$admincourse);
if($conn->connect_error)
{
    die("connection error".$conn->connect_error);
}
$totalTeachers = 20; 
$totalStudents = 500;
try
{
    $result = $conn->query("SELECT COUNT(*) AS total FROM examiner");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalTeachers = $row['total'];
    } else {
        $totalTeachers = 0;
    }
}
catch(mysqli_sql_exception $e)
{
    $totalTeachers = 20; 
}

try
{
    $result = $conn->query("SELECT COUNT(*) AS total FROM student_info");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalStudents = $row['total'];
    } else {
        $totalStudents = 0;
    }
}
catch(mysqli_sql_exception $e)
{
$totalStudents = 500;
}

 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style/admin_dashboard.css">
    <script>
            document.addEventListener("DOMContentLoaded", () => {
        const countUp = (elementId) => {
            const element = document.getElementById(elementId);
            const targetNumber = parseInt(element.getAttribute("data-count"), 10);
            const duration = 2000; // Duration in milliseconds
            let startNumber = 0;
            const increment = Math.ceil(targetNumber / (duration / 50)); // Smoothness of animation

            const counter = setInterval(() => {
                startNumber += increment;
                if (startNumber >= targetNumber) {
                    element.textContent = targetNumber;
                    clearInterval(counter);
                } else {
                    element.textContent = startNumber;
                }
            }, 50); // Updates every 50ms
        };

        // Call countUp for each element
        countUp("teacher-count");
        countUp("student-count");
    });

    </script>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar hidden" id="sidebar">
            <div class="sidebar-header">
                <h2><?php echo $adminusername; ?></h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="my_profile.php"><i class="fas fa-user"></i>My Profile</a></li>
                <li><a href="student_list.php"><i class="fas fa-chart-bar"></i>Students</a></li>
                <li><a href="examinerlist.php"><i class="fas fa-cog"></i>Teachers</a></li>
                <li><a href="adremovesubject.php"><i class="fas fa-sign-out-alt"></i>Manage Subjects</a></li>
                <li><a href="create_student.php"><i class="fas fa-sign-out-alt"></i>Add Student</a></li>
                <li><a href="student_list.php"><i class="fas fa-sign-out-alt"></i>Change Password for Student</a></li>
                <li><a href="examinerlist.php"><i class="fas fa-sign-out-alt"></i>Change Password for Teacher</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
                <header class="dashboard-header">
                <h1>Welcome to Admin Dashboard</h1>
                <p class="subheader">Manage your system efficiently and perform quick actions with ease.</p>
            </header>

            <!-- Quick Actions Section -->
            <section class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-grid">
                <div class="action-card" onclick="location.href='create_students.php'">
                    <div class="action-logo">
                        <img src="../images/createstudent.jpeg" alt="Create Student">
                    </div>
                    <h3>Create Students</h3>
                    <p>Add new students to the system quickly and efficiently.</p>
                </div>

                <div class="action-card" onclick="location.href='create_examiner.php'">
                    <div class="action-logo">
                        <img src="../images/createexaminer.png" alt="Create Teacher">
                    </div>
                    <h3>Create Teacher</h3>
                    <p>Register teachers and assign them to their respective courses.</p>
                </div>
                 
                <div class="action-card" onclick="location.href='student_list.php'">
                    <div class="action-logo">
                        <img src="../images/managestudent.jpeg" alt="Manage Students">
                    </div>
                    <h3>Manage Students</h3>
                    <p>View, update, or remove student information in the system.</p>
                </div>

                <div class="action-card" onclick="location.href='examinerlist.php'">
                    <div class="action-logo">
                        <img src="../images/manageexaminer.jpeg" alt="Manage Teachers">
                    </div>
                    <h3>Manage Teachers</h3>
                    <p>Monitor and update teacher details and course assignments.</p>
                </div>
            </div>
        </section>
        <br><br><br>


         <!-- Main Content Section -->
    <section class="content-area">
        <div class="stat-card total-teachers">
            <h1 id="teacher-count" data-count="<?php echo $totalTeachers; ?>">0</h1>
            <h2>Teachers</h2>
        </div>
        <div class="stat-card total-students">
            <h1 id="student-count" data-count="<?php echo $totalStudents; ?>">0</h1>
            <h2>Students</h2>
        </div>
    </section>


        </div>

    </div>
</body>
</html>
