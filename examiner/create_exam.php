<?php
session_start();
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']); // Clear errors after retrieving them

// Redirect to login if the user is not authenticated
if (!isset($_SESSION['examinerusername']) || !isset($_SESSION['examinercourse'])) {
    header("Location: examiner_login.php");
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', $_SESSION['examinercourse']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch semester count
$semesters = 0;
$sql = "SELECT semesters FROM `admin_info` WHERE `course` = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['examinercourse']);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    $semesters = $row['semesters'];
}
$stmt->close();

// Function to validate form data
function validateForm($data) {
    $errors = [];

    // Trim input values
    $examName = trim($data['exam_name'] ?? '');
    $totalQuestions = trim($data['total_questions'] ?? '');
    $perQuestionMarks = trim($data['per_question_marks'] ?? '');
    $totalMarks = trim($data['total_marks'] ?? '');
    $duration = trim($data['duration'] ?? '');
    $examDate = trim($data['exam_date'] ?? '');
    $examTime = trim($data['exam_time'] ?? '');
    $negativeMarks = isset($data['negative_marks']) ? trim($data['negative_marks']) : 0;
    $sliderChecked = isset($data['negative_marks_slider']);

    // Exam Name Validation
    if (empty($examName)) {
        $errors['exam_name'] = "Exam Name is required.";
    } elseif (strlen($examName) < 3) {
        $errors['exam_name'] = "Exam Name must be at least 3 characters long.";
    }

    // Total Questions Validation
    if (!ctype_digit($totalQuestions) || $totalQuestions <= 0) {
        $errors['total_questions'] = "Total Questions must be a positive whole number.";
    }

    // Per Question Marks Validation
    if (!ctype_digit($perQuestionMarks) || $perQuestionMarks <= 0) {
        $errors['per_question_marks'] = "Per Question Marks must be a positive whole number.";
    }

    // Total Marks Validation
    if (!ctype_digit($totalMarks) || $totalMarks <= 0) {
        $errors['total_marks'] = "Total Marks must be a positive whole number.";
    } 

    // Duration Validation
    if (!ctype_digit($duration) || $duration <= 0) {
        $errors['duration'] = "Duration must be a positive whole number (minutes).";
    }

    // Exam Date Validation
    if (empty($examDate)) {
        $errors['exam_date'] = "Exam Date is required.";
    } elseif ($examDate < date('Y-m-d')) {
        $errors['exam_date'] = "Exam Date cannot be in the past.";
    }

    // Exam Time Validation
    if (empty($examTime)) {
        $errors['exam_time'] = "Exam Time is required.";
    }

  
  // Negative Marks Validation (only if the slider is checked)
    if ($negativeMarks < 0) {
        $errors['negative_marks'] = "Negative Marks must be a non-negative whole number.";
    } 


    return $errors;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'exam_name' => $_POST['exam_name'] ?? '',
        'total_questions' => $_POST['total_questions'] ?? '',
        'per_question_marks' => $_POST['per_question_marks'] ?? '',
        'total_marks' => $_POST['total_marks'] ?? '',
        'duration' => $_POST['duration'] ?? '',
        'exam_date' => $_POST['exam_date'] ?? '',
        'exam_time' => $_POST['exam_time'] ?? '',
        'negative_marks' => $_POST['negative_marks'] ?? '',
        'negative_marks_slider' => isset($_POST['negative_marks_slider']) ? 'on' : ''
    ];

    $errors = validateForm($data);
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: " . $_SERVER['PHP_SELF']); // Refresh page with errors
        exit;
    }

    // No errors, proceed to insert into the database
    submitForm($data);
}

// Function to insert exam details into the database
function submitForm($data) {
    global $conn;

    // Ensure session variables exist
    if (!isset($_SESSION['examinercourse'], $_SESSION['subject_id'], $_SESSION['examinerusername'])) {
        die("Required session variables are missing.");
    }

    // Prepare variables
    $examName = $data['exam_name'];
    $subjectId = $_SESSION['subject_id'];
    $examiner = $_SESSION['examinerusername'];
    $totalQuestions = intval($data['total_questions']);
    $perQuestionMarks = intval($data['per_question_marks']);
    $totalMarks = intval($data['total_marks']);
    $examTime = $data['exam_time'];
    $examDate = $data['exam_date'];
    $duration = intval($data['duration']);
    $negativeMarks = isset($data['negative_marks']) ? intval($data['negative_marks']) : null;
    $semester = intval($_POST['semester']);

    // Check if "exams" table exists
    $checkTableSql = "SHOW TABLES LIKE 'exams'";
    $result = $conn->query($checkTableSql);

    if ($result->num_rows === 0) {
        // Create table if it doesn't exist
        $createTableSql = "
            CREATE TABLE `exams` (
                `exam_id` INT AUTO_INCREMENT PRIMARY KEY,
                `exam_name` VARCHAR(100) NOT NULL,
                `subject_id` INT NOT NULL,
                `examinerusername` VARCHAR(100) NOT NULL,
                `total_question` INT NOT NULL,
                `perquestion_mark` INT NOT NULL,
                `total_marks` INT NOT NULL,
                `timeofexam` TIME NOT NULL,
                `dateofexam` DATE NOT NULL,
                `timelimit` INT NOT NULL,
                `negative_mark` INT DEFAULT NULL,
                `semester` INT NOT NULL
            ) ENGINE = MyISAM;
        ";
        $conn->query($createTableSql);
    }

    // Insert exam details
    $sql = "INSERT INTO `exams` (`exam_name`, `subject_id`, `examinerusername`, `total_question`, `perquestion_mark`, `total_marks`, `timeofexam`, `dateofexam`, `timelimit`, `negative_mark`, `semester`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisiiissiii", $examName, $subjectId, $examiner, $totalQuestions, $perQuestionMarks, $totalMarks, $examTime, $examDate, $duration, $negativeMarks, $semester);
    
    if ($stmt->execute()) {
        $_SESSION['exam_id'] = $conn->insert_id; // Store exam ID in session
        header("Location: addquestionwithexcel.php?exam_id=" . $_SESSION['exam_id'] . "&total_question=" . $totalQuestions);
        exit;
    } else {
        echo "Error adding exam.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Form with Validation</title>
    <link rel="stylesheet" href="style/create_exam.css">
  
</head>
<body>
<a href="examiner_dashboard.php" class="back-button">&larr;</a>
<div class="process-container">
    <div class="step step-blue">1<br>Exam Information</div>
    <div class="connector"></div>
    <div class="step step-gray">2<br>Upload Excel File</div>
</div>

<h1>1.Exam Information</h1>
<form method="POST" action="" >
    <div class="form-group">
         <div class="error">
            <?php
        if (!empty($errors)) {
            echo '<ul>';
            foreach ($errors as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
        }
            ?>

         </div>
    </div>
    <div class="form-group">
        <label for="semester">Semester:</label>
        <select id="semester" name="semester" required>
            <?php
            for ($i = 1; $i <= $semesters; $i++) {
                echo "<option value='$i'>Semester $i</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <label for="exam_name">Exam Name:</label>
        <input type="text" id="exam_name" name="exam_name" required>
    </div>

    <div class="form-group">
        <label for="total_questions">Total Questions:</label>
        <input type="number" id="total_questions" name="total_questions" required>
    </div>

    <div class="form-group">
        <label for="per_question_marks">Per Question Marks:</label>
        <input type="number" id="per_question_marks" name="per_question_marks" required>
    </div>

    <div class="form-group">
        <label for="total_marks">Total Marks:</label>
        <input type="number" id="total_marks" name="total_marks" required>
    </div>

    <div class="form-group">
            <label for="negative_marks_slider">Enable Negative Marks:</label>
        <input type="number" id="negative_marks" name="negative_marks" placeholder="Enter negative marks" value=0 required>
    </div>

    <div class="form-group">
        <label for="duration">Duration of Exam (in minutes):</label>
        <input type="number" id="duration" name="duration" required>
    </div>

    <div class="form-group">
        <label for="exam_date">Date of Exam:</label>
        <input type="date" id="exam_date" name="exam_date" required>
    </div>

    <div class="form-group">
        <label for="exam_time">Time of Exam:</label>
        <input type="time" id="exam_time" name="exam_time" required>
       
    </div>

    <button type="submit" name="submit">Submit</button>
   
</form>
</body>
</html>
