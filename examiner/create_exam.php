<?php
session_start();

// Check if user is not logged in (session variables not set)
if (!isset($_SESSION['examinerusername']) || !isset($_SESSION['examinercourse'])) {
    // Redirect to login page
    header("Location: examiner_login.php");
    exit;
}

// Establish database connection
$conn = new mysqli('localhost', 'root', '', $_SESSION['examinercourse']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$semesters = 0;
$sql = "SELECT * FROM `admin_info` WHERE `course` LIKE '" . $_SESSION['examinercourse'] . "'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    // Fetch the row
    $row = $result->fetch_assoc();
    $semesters = $row['semesters'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Form with Validation</title>
    <link rel="stylesheet" href="style/create_exam.css">
    <script>
    function validateForm(event) {
        const examName = document.getElementById('exam_name').value.trim();
        const totalQuestions = document.getElementById('total_questions').value.trim();
        const perQuestionMarks = document.getElementById('per_question_marks').value.trim();
        const totalMarks = document.getElementById('total_marks').value.trim();
        const duration = document.getElementById('duration').value.trim();
        const date = document.getElementById('exam_date').value.trim();
        const time = document.getElementById('exam_time').value.trim();

        // Get slider and negative marks input
        const slider = document.getElementById('negative_marks_slider');
        const negativeMarksInput = document.getElementById('negative_marks');

        let isValid = true;

        // Clear previous errors
        document.querySelectorAll('.error').forEach(el => el.textContent = '');

        // Validate Exam Name
        if (examName === "") {
            document.getElementById('exam_name_error').textContent = "Exam Name is required.";
            isValid = false;
        }

        // Validate Total Questions
        if (totalQuestions === "" || isNaN(totalQuestions) || totalQuestions <= 0) {
            document.getElementById('total_questions_error').textContent = "Total Questions must be a positive number.";
            isValid = false;
        }

        // Validate Per Question Marks
        if (perQuestionMarks === "" || isNaN(perQuestionMarks) || perQuestionMarks <= 0) {
            document.getElementById('per_question_marks_error').textContent = "Per Question Marks must be a positive number.";
            isValid = false;
        }

        // Validate Total Marks
        if (totalMarks === "" || isNaN(totalMarks) || totalMarks <= 0) {
            document.getElementById('total_marks_error').textContent = "Total Marks must be a positive number.";
            isValid = false;
        }

        // Validate Duration
        if (duration === "" || isNaN(duration) || duration <= 0) {
            document.getElementById('duration_error').textContent = "Duration must be a positive number.";
            isValid = false;
        }

        // Validate Date of Exam
        if (date === "") {
            document.getElementById('exam_date_error').textContent = "Date of Exam is required.";
            isValid = false;
        }

        // Validate Time of Exam
        if (time === "") {
            document.getElementById('exam_time_error').textContent = "Time of Exam is required.";
            isValid = false;
        }

        // Validate Negative Marks (if slider is enabled)
        if (slider.checked) {
            const negativeMarks = negativeMarksInput.value.trim();
            if (negativeMarks === "" || isNaN(negativeMarks) || negativeMarks < 0) {
                document.getElementById('negative_marks_error').textContent = "Negative Marks must be a non-negative number.";
                isValid = false;
            }
        }

        // Prevent form submission if validation fails
        if (!isValid) {
            event.preventDefault();
        }
    }

    function toggleNegativeMarks() {
        const negativeMarksInput = document.getElementById('negative_marks');
        const slider = document.getElementById('negative_marks_slider');
        negativeMarksInput.disabled = !slider.checked;
        if (!slider.checked) {
            negativeMarksInput.value = ""; // Clear value when disabled
        }
    }
    </script>
</head>
<body>
<a href="examiner_dashboard.php" class="back-button">&larr;</a>
<div class="process-container">
    <div class="step step-blue">1<br>Exam Information</div>
    <div class="connector"></div>
    <div class="step step-gray">2<br>Upload Excel File</div>
</div>

<h1>1.Exam Information</h1>
<form method="POST" action="" onsubmit="validateForm(event)">
    <div class="form-group">
        <label for="semester">Semester:</label>
        <select id="semester" name="semester">
            <?php
            for ($i = 1; $i <= $semesters; $i++) {
                echo "<option value='$i'>Semester $i</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <label for="exam_name">Exam Name:</label>
        <input type="text" id="exam_name" name="exam_name">
        <div id="exam_name_error" class="error"></div>
    </div>

    <div class="form-group">
        <label for="total_questions">Total Questions:</label>
        <input type="number" id="total_questions" name="total_questions">
        <div id="total_questions_error" class="error"></div>
    </div>

    <div class="form-group">
        <label for="per_question_marks">Per Question Marks:</label>
        <input type="number" id="per_question_marks" name="per_question_marks">
        <div id="per_question_marks_error" class="error"></div>
    </div>

    <div class="form-group">
        <label for="total_marks">Total Marks:</label>
        <input type="number" id="total_marks" name="total_marks">
        <div id="total_marks_error" class="error"></div>
    </div>

    <div class="form-group">
        <div class="slider-label">
            <label for="negative_marks_slider">Enable Negative Marks:</label>
            <input type="checkbox" id="negative_marks_slider" onclick="toggleNegativeMarks()">
        </div>
        <input type="number" id="negative_marks" name="negative_marks" placeholder="Enter negative marks" disabled>
        <div class="error" id="negative_marks_error"></div>
    </div>

    <div class="form-group">
        <label for="duration">Duration of Exam (in minutes):</label>
        <input type="number" id="duration" name="duration">
        <div id="duration_error" class="error"></div>
    </div>

    <div class="form-group">
        <label for="exam_date">Date of Exam:</label>
        <input type="date" id="exam_date" name="exam_date">
        <div id="exam_date_error" class="error"></div>
    </div>

    <div class="form-group">
        <label for="exam_time">Time of Exam:</label>
        <input type="time" id="exam_time" name="exam_time">
        <div id="exam_time_error" class="error"></div>
    </div>

    <button type="submit" name="submit">Submit</button>
    <?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   

    // Ensure session variables are set
    if (!isset($_SESSION['examinercourse'], $_SESSION['subject_id'], $_SESSION['examinerusername'])) {
        die("Required session variables are missing.");
    }

    // Establish database connection
    $conn = new mysqli('localhost', 'root', '', $_SESSION['examinercourse']);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve form data
    $examname = $_POST['exam_name'];
    $subject_id = intval($_SESSION['subject_id']);
    $examiner = $_SESSION['examinerusername'];
    $totalquestion = intval($_POST['total_questions']);
    $perquestionmarks = intval($_POST['per_question_marks']);
    $totalmarks = intval($_POST['total_marks']);
    $timeofexam = $_POST['exam_time'];
    $dateofexam = $_POST['exam_date'];
    $timelimit = intval($_POST['duration']);
    $negativemarks = isset($_POST['negative_marks']) ? intval($_POST['negative_marks']) : null;
    $semester=intval($_POST['semester']);
         
    // Check if the table already exists
    $checkTableSql = "SHOW TABLES LIKE 'exams'";
    $result = $conn->query($checkTableSql);

    if($result->num_rows > 0)
    {
        //insert the data
            $sql = "INSERT INTO `exams` (`exam_name`, `subject_id`, `examinerusername`, `total_question`, `perquestion_mark`, `total_marks`, `timeofexam`, `dateofexam`, `timelimit`, `negative_mark`,`semester`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
               $stmt = $conn->prepare($sql);
               $stmt->bind_param("sisiiissiii",$examname,$subject_id,$examiner,$totalquestion,$perquestionmarks,$totalmarks,$timeofexam,$dateofexam,$timelimit,$negativemarks,$semester);
               if($stmt->execute())
               {
                $sql = "SELECT exam_id FROM exams WHERE exam_name = '$examname'";
                $result = $conn->query($sql);
                $exam_id='';
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $exam_id = $row['exam_id'];
                    $_SESSION['exam_id']=$exam_id; //session 
                } else {
                    echo "No exam found with the name '$examname'.";
                }
                
            
                echo "<h3 style='color: green;'>$examname and $exam_id is added..<br>Now you can  <a href='addquestionwithexcel.php?exam_id=".$exam_id."&total_question=".$totalquestion."'>Upload the question File</a> </h3>";
                echo "<script>window.location.href='addquestionwithexcel.php?exam_id=".$exam_id."&total_question=".$totalquestion."';</script>";
                exit;
               }
               else
               {
                     echo "not added..";
               }
    }
    else
    {
        //create the table
          // SQL to create the table
                $sql = "
                    CREATE TABLE `exams` (
                        `exam_id` INT AUTO_INCREMENT NOT NULL,
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
                        `semester` INT NOT NULL,
                        PRIMARY KEY (`exam_id`)
                    ) ENGINE = MyISAM;
                ";

                // Execute the query
                if ($conn->query($sql) === TRUE) {
                    $sql = "INSERT INTO `exams` (`exam_name`, `subject_id`, `examinerusername`, `total_question`, `perquestion_mark`, `total_marks`, `timeofexam`, `dateofexam`, `timelimit`, `negative_mark`,`semester`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                       $stmt = $conn->prepare($sql);
                       $stmt->bind_param("sisiiissiii",$examname,$subject_id,$examiner,$totalquestion,$perquestionmarks,$totalmarks,$timeofexam,$dateofexam,$timelimit,$negativemarks,$semester);
                       if($stmt->execute())
                       {
                        $sql = "SELECT exam_id FROM exams WHERE exam_name = '$examname'";
                        $result = $conn->query($sql);
                        $exam_id='';
                        if ($result && $result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $exam_id = $row['exam_id'];
                            $_SESSION['exam_id']=$exam_id; //session 
                        } else {
                            echo "No exam found with the name '$examname'.";
                        }
                        echo "<h3 style='color: green;'>$examname is added..<br>Now you can  <a href='addquestionwithexcel.php?exam_id=".$exam_id."&total_question=".$totalquestion."'>Upload the question File</a> </h3>";
                        echo "<script>window.location.href='addquestionwithexcel.php?exam_id=".$exam_id."&total_question=".$totalquestion."';</script>";
                        exit;
                       }
                       else
                       {
                             echo "not added..";
                       }

                } else {
                    echo "Error creating table: " . $conn->error;

                }
    }
  

    // Close the connection
    $conn->close();
}
?> 
</form>
</body>
</html>
