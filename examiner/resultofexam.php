<?php
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
    $stmt = $conn->prepare("SELECT * FROM exams WHERE examinerusername = ?");
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

// Handle AJAX request
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exam_id=$_POST['exam_id'];
    $semester=$_POST['semester'];
    $attended = [];
    $remaining = [];
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
        $stmt = $conn->prepare("SELECT total_marks, obtain_marks FROM ".$student['student_id']."_student WHERE exam_id = ? AND id = ?");
        $stmt->bind_param("ii", $exam_id, $student['student_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $temp=$student;
            $temp['total_marks'] = $row["total_marks"];
            $temp['obtain_marks'] = $row["obtain_marks"];
            $attended[] = $temp;
        } else {
            $remaining[]=$student;
        }
        $stmt->close();
    }


    
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam List</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Select Exam</h1>
    <form>
        <label for="exam">Choose an exam:</label>
        <select name="exam" id="exam">
            <option value="">-- Select Exam --</option>
            <?php foreach ($list as $exam): ?>
                <option value="<?php echo htmlspecialchars($exam['exam_id']); ?>">
                    <?php echo htmlspecialchars($exam['exam_name']) . " (Semester: " . htmlspecialchars($exam['semester']) . ")"; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <h2>Attended Students</h2>
    <div class="attendstudent"></div>

    <h2>Remaining Students</h2>
    <div class="remainingstudent"></div>

    <script>
        $(document).ready(function() {
            $('#exam').change(function() {
                var exam_id = $(this).val();
                if (exam_id) {
                    $.ajax({
                        type: 'POST',
                        url: '', // Same page
                        data: { exam_id: exam_id },
                        success: function(response) {
                            var data = JSON.parse(response);
                            $('.attendstudent').html('<b>Attended Students:</b> ' + data.attended);
                            $('.remainingstudent').html('<b>Remaining Students:</b> ' + data.remaining);
                        }
                    });
                } else {
                    $('.attendstudent').html('');
                    $('.remainingstudent').html('');
                }
            });
        });
    </script>
</body>
</html>
