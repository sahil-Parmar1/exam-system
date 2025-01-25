<?php
session_start();
require '../vendor/autoload.php'; // Load PhpSpreadsheet library
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

$exam_id = $_SESSION['exam_id'];

// Database connection
$servername = "localhost"; // Update with your DB server
$username = "root"; // Update with your DB username
$password = ""; // Update with your DB password
$dbname = $_SESSION['examinercourse']; // Update with your DB name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch exam name from exams table
$exam_name_sql = "SELECT exam_name FROM exams WHERE exam_id = ?";
$stmt = $conn->prepare($exam_name_sql);
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$stmt->bind_result($exam_name);
$stmt->fetch();
$stmt->close();
// Fetch records from `$exam_id_exam` table
$table_name = $exam_id . '_exam';
// Check if the table exists
$table_check_sql = "SHOW TABLES LIKE '$table_name'";
$table_check_result = $conn->query($table_check_sql);

if ($table_check_result->num_rows == 0) {
    echo "<script>alert('The table for this exam does not exist.'); window.history.back();</script>";
    exit;
}
$sql = "SELECT id, type, question_option, ans, question FROM `$table_name`";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Create a new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    

    // Add column headers
    $sheet->setCellValue('A1', 'ID')
          ->setCellValue('B1', 'Type')
          ->setCellValue('C1', 'Question Option')
          ->setCellValue('D1', 'Answer')
          ->setCellValue('E1', 'Question');

    // Populate data rows
    $row = 2; // Start from the second row
    while ($data = $result->fetch_assoc()) {
        $sheet->setCellValue("A$row", $data['id'])
              ->setCellValue("B$row", $data['type'])
              ->setCellValue("C$row", $data['question_option'])
              ->setCellValue("D$row", $data['ans'])
              ->setCellValue("E$row", $data['question']);
        $row++;
    }

    // Set the HTTP headers for file download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$exam_name.'.xlsx"');
    header('Cache-Control: max-age=0');

    // Write the spreadsheet to output
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} else {
    echo "No records found for this exam.";
}

$conn->close();
?>
