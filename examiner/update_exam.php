<?php
session_start();


if (isset($_GET['exam_id'])) {
    $_SESSION['exam_id'] = $_GET['exam_id'];
}

if (!isset($_SESSION['examinerusername']) || !isset($_SESSION['examinercourse'])) {
    // Redirect to login page
    header("Location: examiner_login.php");
    exit;
}

if (!isset($_SESSION['exam_id'])) {
    header("Location: examiner_dashboard.php");
    exit;
}
$conn = new mysqli("localhost", "root", "", $_SESSION['examinercourse']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$exam_id = $_SESSION['exam_id'];
$table_name=$exam_id."_exam";
// Check if the table exists
$table_check_sql = "SHOW TABLES LIKE '$table_name'";
$table_check_result = $conn->query($table_check_sql);

if ($table_check_result->num_rows == 0) {
    echo "<script>alert('The table for this exam does not exist.'); window.history.back();</script>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Exam</title>
    <link rel="stylesheet" href="style/update_exam.css">
</head>
<body>
<a href="examiner_dashboard.php" class="back-button">&larr;</a>
<div class="header">Update Exam</div>
    <?php
    // Database connection
$servername = "localhost"; // Update with your DB server
$username = "root"; // Update with your DB username
$password = ""; // Update with your DB password
$dbname = $_SESSION['examinercourse']; // Update with your DB name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$table_name = $exam_id . '_exam';
$sql = "SELECT * FROM $table_name";
$result = $conn->query($sql);
if($result && $result->num_rows > 0)
{
    echo "<div class='header'>Correction on questions</div>";
    echo "<table border='1'>
    <tr>
    <th>ID</th>
    <th>Type</th>
    <th>Question Option</th>
    <th>Answer</th>
    <th>Question</th>
     <th>Update</th>
    </tr>";
    while($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $type = $row['type'];
        $question_option = $row['question_option'];
        $ans = $row['ans'];
        $question = $row['question'];
        echo "<tr>
        <td>$id</td>
        <td>$type</td>
        <td>$question_option</td>
        <td>$ans</td>
        <td>$question</td>
        <td><a href='update_question.php?id=$id&exam_id=$exam_id'>Update</a></td>
        </tr>";
      }
        echo "</table>";
    }
 
else
{
    echo "Exam not found";
    exit;
}

$conn->close();?>
</body>
</html>
