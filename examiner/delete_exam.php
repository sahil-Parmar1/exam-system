<?php
session_start();
if (!isset($_SESSION['examinerusername']) || !isset($_SESSION['examinercourse'])) {
    // Redirect to login page
    header("Location: examiner_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Exam</title>

</head>
<body>
<?php
if (isset($_GET['exam_id'])) {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = $_SESSION['examinercourse'];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $exam_id = intval($_GET['exam_id']); // Sanitize the input

    // Delete record query
    $sql = "DELETE FROM exams WHERE exam_id = $exam_id";
    if ($conn->query($sql) === TRUE) {
        // Drop the associated table
        $dropTableSql = "DROP TABLE IF EXISTS " . $exam_id . "_exam";
        $conn->query($dropTableSql);

        echo "<script>alert('Record deleted successfully'); window.location.href = 'manage_exam.php';</script>";
    } else {
        echo "<script>alert('Error deleting record: " . $conn->error . "'); window.location.href = 'manage_exam.php';</script>";
    }

    $conn->close();
}
?>
</body>
</html>
