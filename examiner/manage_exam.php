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
    <title>Manage Exam</title>
    <link rel="stylesheet" href="style/manage_exam.css">
</head>
<script>
        // JavaScript function to confirm deletion
        function confirmDeletion(examId) {
            if (confirm("Are you sure you want to delete this exam? This action cannot be undone.")) {
                // Redirect to the same script with the exam_id to delete
                window.location.href = "delete_exam.php?exam_id=" + examId;
            } else {
                // Redirect back to the previous page
                
            }
        }
    </script>
<body>
  <a href="examiner_dashboard.php" class="back-button">&larr;</a><br><br>
    <h1>Manage Exam</h1>
    <h2></h2>
    <?php
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = $_SESSION['examinercourse'];

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch data from database
    // Check if the exams table exists
    $tableCheckSql = "SHOW TABLES LIKE 'exams'";
    $tableCheckResult = $conn->query($tableCheckSql);

    if ($tableCheckResult->num_rows == 0) {
        echo "You have not created any exams yet.want to create one? <a href='create_exam.php'>Create Exam</a>";
    }
    else
    {
        $sql = "SELECT * FROM exams WHERE examinerusername = '" . $_SESSION['examinerusername'] . "'";
        $result = $conn->query($sql);
    
        if ($result &&$result->num_rows > 0) {
            // Output data of each row
            echo "  <table>
            <tr>
                <td>Exam Name</td> 
                <td>Total Marks</td>
                <td>Total question</td> 
                <td>Date of exam</td>
                <td>Time of exam</td>
                <td>Add questions on Upload excel file</td>
                <td>Download</td>
                <td>Update Question</td>
                <td>Add Question (Individually)</td>
                <td>delete</td>
            </tr>";
            while($row = $result->fetch_assoc()) {
                // Check if the exam_id_exam table exists
                $tableCheckSql = "SHOW TABLES LIKE '" . $row['exam_id'] . "_exam'";
                $tableCheckResult = $conn->query($tableCheckSql);
                  
                if ($tableCheckResult && $tableCheckResult->num_rows > 0) {
                   $uploaded=true;  
                } else {
                    $uploaded=false;
                }
          
                echo "
            <tr>
                <td>".$row['exam_name']."</td>
          
                <td>".$row['total_marks']."</td>
           
                <td>".$row['total_question']."</td>
           
                <td>".$row['dateofexam']."</td>
           
                <td>".$row['timeofexam']."</td>";
                      if($uploaded){
                          echo "<td style='color: green;'>Uploaded</td>";
                      }else{
                        echo "<td><a href='addquestionwithexcel.php?exam_id=" . $row['exam_id'] . "&total_question=".$row['total_question']."'>Upload questions</a></td>";
                      }
            echo "<td><a href='download_exam_data.php?exam_id=" . $row['exam_id'] . "'>⏬</a></td>
            <td><a href='update_exam.php?exam_id=" . $row['exam_id'] . "'>📝</a></td>
            <td><a href='question_add.php?exam_id=" . $row['exam_id'] . "'>add</a></td>
            
            <td><button id='delete' onclick='confirmDeletion(".$row['exam_id'].")'>🗑️</button></td>
            </tr>";
               
            }
            echo "</table>";
        } else {
            echo "You have not created any exams yet.want to create one? <a href='create_exam.php'>Create Exam</a>";
        }
    }
   
    $conn->close();
    ?>
</body>
</html>
