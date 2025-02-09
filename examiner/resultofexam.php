<?php
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
session_start();
if (!isset($_SESSION['examinerusername']) || !isset($_SESSION['examinercourse'])) {
    header("Location: examiner_login.php");
    exit;
}

// Database Configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = $_SESSION['examinercourse'];
$examinerusername = $_SESSION['examinerusername'];
$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch exams for the dropdown
$list = [];
if ($examinerusername) {
    $stmt = $conn->prepare("SELECT exam_id, exam_name, semester FROM exams WHERE examinerusername = ?");
    $stmt->bind_param("s", $examinerusername);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $list[] = [
            'exam_id' => $row["exam_id"],
            'exam_name' => $row["exam_name"],
            'semester' => $row["semester"]
        ];
    }
    $stmt->close();
}

$attended = [];
$remaining = [];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['exam'])) {
    
    function handleExamSubmission($conn, &$attended, &$remaining) {
        $temp = json_decode($_POST['exam'], true);  // Decode JSON input
             
        if ($temp && isset($temp['exam_id'], $temp['semester'])) {
            $exam_id = $temp['exam_id'];
            $semester = $temp['semester'];
    
            $students = [];
            $stmt = $conn->prepare("SELECT id, roll, username FROM student_info WHERE semester = ?");
            $stmt->bind_param("s", $semester);
            $stmt->execute();
            $result = $stmt->get_result();
    
            while ($row = $result->fetch_assoc()) {
                $students[] = [
                    'student_id' => $row["id"],
                    'roll' => $row["roll"],
                    'username' => $row["username"]
                ];
            }
            $stmt->close();
    
            foreach ($students as $student) {
              
                try {
                    $table_name = $student['student_id'] . "_student"; 

                    // Check if table exists
                    $checkTableQuery = "SHOW TABLES LIKE '$table_name'";
                    $checkTableResult = $conn->query($checkTableQuery);

                    if ($checkTableResult->num_rows == 0) {
                        // Table does not exist, skip this student
                        $remaining[] = $student;
                        continue;
                    }

                    $query = "SELECT total_marks, obtain_marks FROM `$table_name` WHERE exam_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $exam_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $student['total_marks'] = $row["total_marks"];
                        $student['obtain_marks'] = $row["obtain_marks"];
                        $attended[] = $student;
                        
                    } else {
                        $remaining[] = $student;
                       
                    }
                    $stmt->close();
                } catch (mysqli_sql_exception $e) {
                    error_log("SQL Error: " . $e->getMessage());
                    $remaining[] = $student;
                    
                } catch (Exception $e) {
                    error_log("Error: " . $e->getMessage());
                    
                }
            }
        }
    }

    function handleExamDownload($conn, $attended) {
        $temp = json_decode($_POST['exam'], true);
        $exam_name = $temp['exam_name'];
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Roll No');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Total Marks');
        $sheet->setCellValue('D1', 'Obtained Marks');
    
        $rowNumber = 2;
        
        foreach ($attended as $student) {
            $sheet->setCellValue('A' . $rowNumber, $student['roll']);
            $sheet->setCellValue('B' . $rowNumber, $student['username']);
            $sheet->setCellValue('C' . $rowNumber, $student['total_marks']);
            $sheet->setCellValue('D' . $rowNumber, $student['obtain_marks']);
            $rowNumber++;
        }
    
        $writer = new Xlsx($spreadsheet);
        $exam_name = str_replace(' ', '_', $exam_name);
        $filename = $exam_name.'_exam_report.xlsx';
    
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    
        $writer->save('php://output');
        exit;
    }
    if (isset($_POST['submit'])) {
        handleExamSubmission($conn, $attended, $remaining);
    } elseif (isset($_POST['download'])) {
        handleExamSubmission($conn, $attended, $remaining);
        handleExamDownload($conn, $attended);
    }




   
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Result</title>
    <link rel="stylesheet" href="style/resultofexam.css"/>

</head>
<body>
    <div class="header">
    <a href="#" onclick="window.location.href='examiner_dashboard.php'" class="back-button">&larr;</a><br><br>
    <h1>Result of Exams</h1>

    </div>
    <form method="POST">
        <label for="exam">Choose an exam:</label>
        <select name="exam" id="exam" required>
            <option value="">-- Select Exam --</option>
            <?php foreach ($list as $exam): ?>
                <option value="<?php echo htmlspecialchars(json_encode($exam)); ?>">
                    <?php echo htmlspecialchars($exam['exam_name']) . " (Semester: " . htmlspecialchars($exam['semester']) . ")"; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="submit">Go</button>
        <button type="submit" name="download">Download A Excel Report</button>
    </form>

    
    <div class="attendstudent">
    <h2>Attended Students</h2>
<table class="attendstudent">
    <thead>
        <tr>
            <th>Roll No</th>
            <th>Name</th>
            <th>Total Marks</th>
            <th>Obtained Marks</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($attended as $student): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['roll']); ?></td>
                <td><?php echo htmlspecialchars($student['username']); ?></td>
                <td><?php echo htmlspecialchars($student['total_marks']); ?></td>
                <td><?php echo htmlspecialchars($student['obtain_marks']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    </div>


    <div class="remainingstudent">
    <h2>Remaining Students</h2>
<table class="remainingstudent">
    <thead>
        <tr>
            <th>Roll No</th>
            <th>username</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($remaining as $student): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['roll']); ?></td>
                <td><?php echo htmlspecialchars($student['username']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    </div>

</body>
</html>
