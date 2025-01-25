<?php
session_start();
if (!isset($_SESSION['studentusername']) || !isset($_SESSION['studentcourse'])) {
    header("Location: student_login.php");
    exit;
}
if (!isset($_GET['exam_id']) && !isset($_GET['studentsemester'])) {
    die("Exam ID is missing.");
    exit;
}
$exam_id = $_GET['exam_id'];
$username=$_SESSION['studentusername'];
$course=$_SESSION['studentcourse'];
$semester=$_GET['studentsemester'];
$conn = new mysqli("localhost", "root", "", $course);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$current_date = date("Y-m-d");
$current_time = date("H:i:s");
$dateofexam = "";
$timeofexam = "";
$sql = "SELECT dateofexam, timeofexam FROM exams WHERE exam_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $dateofexam = $row['dateofexam'];
    $timeofexam = $row['timeofexam'];
} else {
    die("Exam not found.");
}

//for demo purpose
//$current_time = "08:59:00"; // Ensure 8 is represented as 08 for valid comparison
$current_date_time = new DateTime("$current_date $current_time");
$exam_date_time = new DateTime("$dateofexam $timeofexam");

if ($current_date_time >= $exam_date_time) {
                $timelimit = 0;
            $sql = "SELECT timelimit FROM exams WHERE exam_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $exam_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $timelimit = $row['timelimit'];
            } else {
                die("Exam not found.");
            }
            $_SESSION['start_time'] = time();
            $_SESSION['end_time'] = $_SESSION['start_time'] + ($timelimit * 60);
    header("Location: exam_page.php?exam_id=$exam_id&studentsemester=$studentsemester");
    exit;
} else {
    echo "<script>alert('The exam is not available at this time. Exam time: $dateofexam $timeofexam'); window.location.href='student_dashboard.php';</script>";
}


?>