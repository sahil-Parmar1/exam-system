<?php
session_start();
if (!isset($_SESSION['studentusername']) || !isset($_SESSION['studentcourse'])) {
    header("Location: student_login.php");
    exit;
}
$username=$_SESSION['studentusername'];
$course=$_SESSION['studentcourse'];
$conn = new mysqli("localhost", "root", "", $course);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//fecth the neccesary info about student
$student_id=0;
$roll=0;
$name='';
$sql="SELECT * FROM `student_info` WHERE `username` LIKE '".$username."'";
$result = $conn->query($sql);
$semester='';
if ($result && $result->num_rows > 0) {
    // Fetch the row
    $row = $result->fetch_assoc();
    $student_id=$row['id'];
    $roll=$row['roll'];
    $name=$row['name'];
    $semester=$row['semester'];
}
else
{
    echo "No student found";
    exit;
}
//frist fecth the student attend exam 
$checkTableSql = "SHOW TABLES LIKE '".$student_id."_student'";
$result = $conn->query($checkTableSql);
$attended_exam=[];
$remaining_exam=[];
if ($result && $result->num_rows > 0) {
        //fecth the table data
       
        $fetchDataSql = "SELECT * FROM `".$student_id."_student`";
        $fetchResult = $conn->query($fetchDataSql);
        if ($fetchResult && $fetchResult->num_rows > 0) {
            while ($row = $fetchResult->fetch_assoc()) {
                $attended_exam[] = $row;
            }
        } else {
            echo "";
        }
        
}
else
{
  
            //create a id_student table
            $studenttable=$student_id."_student";
            $createTableSql =  "CREATE TABLE `".$student_id."_student` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                exam_id INT NOT NULL,
                obtain_marks FLOAT NOT NULL,
                total_marks FLOAT NOT NULL
            )";
            
            if ($conn->query($createTableSql) === TRUE) {
              
                //fecth the table data
                    
                    $fetchDataSql = "SELECT * FROM `".$student_id."_student`";
                    $fetchResult = $conn->query($fetchDataSql);
                    if ($fetchResult && $fetchResult->num_rows > 0) {
                        while ($row = $fetchResult->fetch_assoc()) {
                            $attended_exam[] = $row;
                        }
                    } else {
                        echo "";
                    }
            } else {
                echo "Error creating table: " . $conn->error;
            }
}

//fecth the raiming exams
$checkExamsTableSql = "SHOW TABLES LIKE 'exams'";
$result = $conn->query($checkExamsTableSql);
if (!$result || $result->num_rows == 0) {
    echo "Exams table does not exist.";
    echo "<div id='examPopup' class='popup'>
                    <div class='popup-content'>
                        <h2>No Exams plateform Available!</h2>
                        <p>student login without examiner Please contact your examiner to create exams for your semester.</p>
                        <button onclick='closePopup()'>Close</button>
                    </div>
                  </div>
                  <script>
                    function closePopup() {
                        document.getElementById('examPopup').style.display = 'none';
                    }
                  </script>
                  <style>
                    .popup {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0, 0, 0, 0.5);
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    }
                    .popup-content {
                        background-color: white;
                        padding: 20px;
                        border-radius: 5px;
                        text-align: center;
                    }
                    .popup-content button {
                        margin-top: 10px;
                    }
                  </style>";
    exit;
}
else
{
    $sql = "SELECT `exam_id` FROM `exams` where semester=$semester";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (!in_array($row['exam_id'], $attended_exam)) {
                $remaining_exam[] = $row['exam_id'];
            }
        }
    } else {
        echo "";
    }  
}

$attended_exam_ids = array_column($attended_exam, 'exam_id');
$remaining_exam = array_filter($remaining_exam, function($exam_id) use ($attended_exam_ids) {
    return !in_array($exam_id, $attended_exam_ids);
});


if($_SERVER['REQUEST_METHOD']=='POST')
{
   if(isset($_POST['logout']))
   {
      session_destroy();
      header("Location: student_login.php");
      exit;
   }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="style/student_dashboard.css">
    
</head>
<body>

    <form action="student_dashboard.php" method="POST">
    <header>
    <div class="header-center">
        <h1>Student Dashboard</h1>
        <h2>Welcome, <?php echo $name; ?>!</h2>
        <h6>Live Date and Time</h6>
        <p id="liveDateTime"></p>
        <h6><button name="logout" onclick="window.location.href='../logout.php'">Logout</button></h6>
    </div>

    <div class="my_profile" onclick="window.location.href='my_profile.php?studentusername=<?php echo $username; ?>'">
        <img src="../images/myprofilestudent.jpg" alt="profile" class="myprofileicon"> My Profile
    </div>
</header>

<script>
    let currentTime = new Date("<?php echo date('Y-m-d H:i:s'); ?>");

    function updateDateTime() {
        currentTime.setSeconds(currentTime.getSeconds() + 1);
        const formattedDate = currentTime.toLocaleDateString();
        const formattedTime = currentTime.toLocaleTimeString();
        document.getElementById("liveDateTime").innerText = `${formattedDate}, ${formattedTime}`;
    }

    setInterval(updateDateTime, 1000);
    updateDateTime();
</script>

   <div class="remaining_exam">
   <?php
if (!empty($remaining_exam)) {
    echo "<h3>Remaining Exams:</h3>";
    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<thead>
            <tr>
                <th>Exam Name</th>
                <th>Subject Name</th>
                <th>Subject Code</th>
                <th>Total Marks</th>
                <th>Date of Exam</th>
                <th>Time of Exam</th>
                <th>Attend</th>
            </tr>
          </thead>";
    echo "<tbody>";

    foreach ($remaining_exam as $exam_id) {
        $sql = "SELECT * FROM `exams` WHERE `exam_id` = $exam_id";
        $examResult = $conn->query($sql);
        if ($examResult && $examResult->num_rows > 0) {
            $examRow = $examResult->fetch_assoc();
            $subject_id = $examRow['subject_id'];

            // Fetch the subject details
            $sql = "SELECT * FROM `subjects` WHERE `id` = $subject_id";
            $subjectResult = $conn->query($sql);
            if ($subjectResult && $subjectResult->num_rows > 0) {
                $subjectRow = $subjectResult->fetch_assoc();
                $subject_name = htmlspecialchars($subjectRow['subject_name']);
                $subject_code = htmlspecialchars($subjectRow['subject_code']);
                $exam_name = htmlspecialchars($examRow['exam_name']);
                $total_marks = htmlspecialchars($examRow['total_marks']);
                $date_of_exam = htmlspecialchars($examRow['dateofexam']);
                $time_of_exam = htmlspecialchars($examRow['timeofexam']);
                $currentDate = date('Y-m-d'); // Format: 2024-12-25
                $currentTime = date('H:i:s'); // Format: 14:30:00 (24-hour format)

                // Display the row in the table
                echo "<tr>
                        <td>$exam_name</td>
                        <td>$subject_name</td>
                        <td>$subject_code</td>
                        <td>$total_marks</td>
                        <td style='color:blue'>$date_of_exam</td>
                        <td style='color:blue'>$time_of_exam</td>
                        <td><a href='exam_check.php?exam_id=$exam_id&studentsemester=$semester'>Attend ⏭</a></td>
                      </tr>";
            } else {
                echo "<tr><td colspan='6'>Subject details not found for Exam ID: $exam_id</td></tr>";
            }
        } else {
            echo "<tr><td colspan='6'>Exam details not found for Exam ID: $exam_id</td></tr>";
        }
    }

    echo "</tbody>";
    echo "</table>";
} else {
    echo "<p>No remaining exams.</p>";
}
?>

    </div>
    <div class="attended_exam">
<?php
if (!empty($attended_exam)) {
     echo "<h3>Attended Exams:</h3>";
     echo "<table border='1' cellspacing='0' cellpadding='5'>";
     echo "<thead>
                <tr>
                     <th>Exam Name</th>
                     <th>Subject Name</th>
                     <th>Subject Code</th>
                     <th>Total Marks</th>
                     <th>Obtain Marks</th>
                </tr>
             </thead>";
     echo "<tbody>";

     foreach ($attended_exam as $exam) {
        $exam_id = $exam['exam_id'];
        $sql = "SELECT * FROM `exams` WHERE `exam_id` = $exam_id";
        $examResult = $conn->query($sql);
        if ($examResult && $examResult->num_rows > 0) {
            $examRow = $examResult->fetch_assoc();
            $subject_id = $examRow['subject_id'];
            $exam_name = htmlspecialchars($examRow['exam_name']);

            // Fetch the subject details
            $sql = "SELECT * FROM `subjects` WHERE `id` = $subject_id";
            $subjectResult = $conn->query($sql);
            if ($subjectResult && $subjectResult->num_rows > 0) {
                $subjectRow = $subjectResult->fetch_assoc();
                $subject_name = htmlspecialchars($subjectRow['subject_name']);
                $subject_code = htmlspecialchars($subjectRow['subject_code']);
            } else {
                $subject_name = "N/A";
                $subject_code = "N/A";
            }
        } else {
            $subject_name = "N/A";
            $subject_code = "N/A";
        }
            $total_marks = htmlspecialchars($exam['total_marks']);
            $obtain_marks = htmlspecialchars($exam['obtain_marks']);          
          echo "<tr>
                    <td>$exam_name</td>
                     <td>$subject_name</td>
                        <td>$subject_code</td>
                        <td>$total_marks</td>
                        <td>$obtain_marks</td>
                  </tr>";
     }

     echo "</tbody>";
     echo "</table>";
} else {
     echo "<h3>Attended Exams:</h3>";
     echo "<p>No attended exams yet.</p>";
}
?>
    </div>
    </form>
</body>
</html>