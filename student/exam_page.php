<?php
session_start();
if (!isset($_SESSION['studentusername'], $_SESSION['studentcourse'],$_GET['studentsemester'])) {
    header("Location: student_login.php");
    exit;
}

if (!isset($_GET['exam_id']) || !is_numeric($_GET['exam_id'])) {
    die("Invalid Exam ID.");
}

$exam_id = intval($_GET['exam_id']);
$username = $_SESSION['studentusername'];
$course = $_SESSION['studentcourse'];
$semester = $_GET['studentsemester'];

// Establish Database Connection
$conn = new mysqli("localhost", "root", "", $course);
if ($conn->connect_error) {
    die("Database connection failed.");
}

//fecth the exam information
$sql = "SELECT exam_name, total_question, perquestion_mark, total_marks, negative_mark FROM exams WHERE exam_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing the statement: " . $conn->error);
}
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        //echo "Exam Name: " . $row["exam_name"]. " - Total Questions: " . $row["total_question"]. " - Per Question Mark: " . $row["perquestion_mark"]. " - Total Mark: " . $row["total_marks"]. " - Negative Mark: " . $row["negative_mark"]. "<br>";
        $_SESSION['exam_name'] = $row["exam_name"];
        $_SESSION['total_question'] = $row["total_question"];
        $_SESSION['perquestion_mark'] = $row["perquestion_mark"];
        $_SESSION['total_marks'] = $row["total_marks"];
        $_SESSION['negative_mark'] = $row["negative_mark"];
    }
} else {
    echo "0 results";
}

if(!isset($_SESSION['question_data']))
{
    $_SESSION['question_data'] = [];
   $_SESSION['question_index'] = -1;
   $_SESSION['question_index_array'] = [];
}
function rand_question_index()
{
    if($_SESSION['total_question'] == count($_SESSION['question_index_array']))
    {
        return -1;
    }
    do {
        $question_index = rand(1, $_SESSION['total_question']);
    } while (in_array($question_index, $_SESSION['question_index_array']));
    
    $_SESSION['question_index_array'][] = $question_index;
    return $question_index;
}
function addquestions()
{
   global $conn;
   global $exam_id;
        $examname=$exam_id."_exam";
        $question_index = rand_question_index();
        $sql = "SELECT * FROM $examname WHERE id=? LIMIT 1 ";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing the statement: " . $conn->error);
        }
        $stmt->bind_param("i",$question_index);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if($row['type']=='Objective')
            {
                $options = explode(',,', $row['question_option']);
                $row['optionA'] = $options[0];
                $row['optionB'] = $options[1];
                $row['optionC'] = $options[2];
                $row['optionD'] = $options[3];
            
            }
            $row['mark']='none';
            $row['userans']='none';
            $_SESSION['question_data'][] = $row;
            //echo "<br><br>row: ";
          // print_r($row);
          // echo "<br><br>question_data: ";
           if(isset($_SESSION['question_index']))
             {
                   $_SESSION['question_index'] = $_SESSION['question_index'] + 1;
             }
             else
             {
                $_SESSION['question_index'] = 0;
             }
             //print_r($_SESSION['question_data']);
             //echo "<br><br>question_index: ";
             //echo "Question Index: ".$_SESSION['question_index'];
        } else {
            echo "No more questions available. $question_index";
        }
}
function marks_cals($index)
{
  if($_SESSION['question_data'][$index]['type'] == 'Objective')
  {
     $userans=isset($_POST['option'])?$_POST['option']:'none';
    }
    else if($_SESSION['question_data'][$index]['type'] == 'fill')
    {
        $userans = isset($_POST['fill'])?strtoupper($_POST['fill']):'none';
    }
    else 
    {
        $userans=isset($_POST['TF'])?$_POST['TF']:'none';
    }
      if($userans == $_SESSION['question_data'][$index]['ans'])
      {
          //add marks
          $mark=$_SESSION['perquestion_mark'];
      }
      else
      {
         //deduct marks
         if(isset($_SESSION['negative_mark']) && $_SESSION['negative_mark'] > 0)
         {
             $mark=0-$_SESSION['negative_mark'];
         }
         else
         {
          $mark=0;
         }
      }
      $_SESSION['question_data'][$index]['mark']=$mark;
      $_SESSION['question_data'][$index]['userans']=$userans;

 
}
// Calculate remaining time
$duration = $_SESSION['end_time'] - time();
if ($duration < 0) {
    $duration = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['next'])) {
        
        
        end($_SESSION['question_data']);
        $lastIndex = key($_SESSION['question_data']);
        if($_SESSION['question_index'] == -1)
        {
            addquestions();
        }
        else if($_SESSION['question_index'] == $lastIndex)
        {
           marks_cals($_SESSION['question_index']); 
           addquestions();
        }
        else if($_SESSION['question_index'] < $lastIndex && $_SESSION['question_index'] >= 0)
        {
            marks_cals($_SESSION['question_index']); 
            $_SESSION['question_index'] = $_SESSION['question_index'] + 1;
        }
      
       
    }
    if(isset($_POST['prev']))
    {
        if($_SESSION['question_index'] > 0)
        {
            marks_cals($_SESSION['question_index']); 
            $_SESSION['question_index'] = $_SESSION['question_index'] - 1;
        }
      

    }
    if(isset($_POST['submit']))
    {
        if($_SESSION['question_index'] > 0)
        {
            marks_cals($_SESSION['question_index']); 
        }
        $total_marks=0;
        foreach($_SESSION['question_data'] as $key => $value)
        {
            $total_marks=$total_marks+$value['mark'];
        }
       // echo "<br><br>Total Marks: ".$total_marks;
       
        $_SESSION['obtain_marks']=$total_marks;

        // Check if student_id table exists
        // Fetch the student ID from the student_info table using the username
        $studentIdSql = "SELECT id FROM student_info WHERE username = ?";
        $studentIdStmt = $conn->prepare($studentIdSql);
        if ($studentIdStmt === FALSE) {
            die("Error preparing the student ID statement: " . $conn->error);
        }
        $studentIdStmt->bind_param("s", $username);
        $studentIdStmt->execute();
        $studentIdResult = $studentIdStmt->get_result();
        $student_id = 0;
        if ($studentIdResult->num_rows > 0) {
            $studentIdRow = $studentIdResult->fetch_assoc();
            $student_id = $studentIdRow['id'];
        } else {
            die("Student ID not found for username: " . $username);
        }
        $studentIdStmt->close();
        $studenttable=$student_id."_student";
        $checkTableSql = "SHOW TABLES LIKE '$studenttable'";
        $tableResult = $conn->query($checkTableSql);

        if ($tableResult->num_rows == 0) {
            // Create student_id table if it does not exist
            $createTableSql = "CREATE TABLE $studenttable (
                id INT AUTO_INCREMENT PRIMARY KEY,
                exam_id INT NOT NULL,
                obtain_marks FLOAT NOT NULL,
                total_marks FLOAT NOT NULL,
               
            )";
            
            if ($conn->query($createTableSql) === FALSE) {
                die("Error creating student_id table: " . $conn->error);
            }
        }

        // Insert the student's exam result into the student_id table
        $insertResultSql = "INSERT INTO $studenttable (exam_id, obtain_marks, total_marks) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertResultSql);
        if ($insertStmt === FALSE) {
            die("Error preparing the insert statement: " . $conn->error);
        }
        $insertStmt->bind_param("idd", $exam_id, $total_marks, $_SESSION['total_marks']);
        $insertStmt->execute();
        $insertStmt->close();
        //redirect to result page
        header("Location: exam_result.php?total_marks=".$_SESSION['total_marks']."&obtain_marks=".$_SESSION['obtain_marks']."&total_question=".$_SESSION['total_question']."&negative_marks=".$_SESSION['negative_mark']);
        // Unset all session variables used in this page
        unset($_SESSION['exam_name']);
        unset($_SESSION['total_question']);
        unset($_SESSION['perquestion_mark']);
        unset($_SESSION['total_marks']);
        unset($_SESSION['negative_mark']);
        unset($_SESSION['question_data']);
        unset($_SESSION['question_index']);
        unset($_SESSION['question_index_array']);
        unset($_SESSION['obtain_marks']);
        exit;
    }
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Timer</title>
    <link rel="stylesheet" href="style/exam_page.css">
  
    <script>
        let duration = <?php echo $duration; ?>;
        
        function startTimer() {
          
            const timerElement = document.getElementById("timer");
            const timer = setInterval(() => {
                if (duration <= 0) {
                    clearInterval(timer);
                    timerElement.textContent = "EXPIRED";
                   
                    // Optionally, redirect to another page
                   
                // Submit the form to end the exam
                document.getElementById("submit").click();
                } else {
                    const minutes = Math.floor(duration / 60);
                    const seconds = duration % 60;
                    timerElement.textContent = `${minutes}m ${seconds}s`;
                    duration--;
                }
            }, 1000);
        }
        window.onload = startTimer;
    </script>
</head>
<body>

<form action="" method="POST">
<div class="header">
    <h1 style="margin: 0; flex: 1; text-align: center; font-size: 24px;"><?php echo htmlspecialchars($_SESSION['exam_name'])?></h1>
    <div style="text-align: right;">
        <h5 style="margin: 0; font-size: 16px;">Exam Timer</h5>
        <p id="timer" style="margin: 0; font-size: 18px; font-weight: bold;">00m 00s</p>
    </div>
</div>

    
<?php
    echo "<div class='container' >";
    
    $index = $_SESSION['question_index'];
    
    if ($index == -1) {
        echo "<div class='exam-info'>";
        echo "<h1 style='margin-bottom: 20px;'>" . htmlspecialchars($_SESSION['exam_name']) . "</h1>";
        echo "<p><strong>Total Questions:</strong> " . htmlspecialchars($_SESSION['total_question']) . "</p>";
        echo "<p><strong>Marks Per Question:</strong> " . htmlspecialchars($_SESSION['perquestion_mark']) . "</p>";
        echo "<p><strong>Total Marks:</strong> " . htmlspecialchars($_SESSION['total_marks']) . "</p>";
        if ($_SESSION['negative_mark'] > 0) {
            echo "<p><strong>Negative Marking:</strong> " . htmlspecialchars($_SESSION['negative_mark']) . "</p>";
        } else {
            echo "<p><strong>Negative Marking:</strong> None</p>";
        }
        echo "<div class='buttons' style='margin-top: 20px;'>";
        echo "<button name='next' style='padding: 10px 20px; border: none; border-radius: 5px; background-color: #4CAF50; color: white; cursor: pointer;'>Start</button>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='question-container' style='width: 50%; padding: 20px; background: white; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 8px; text-align: left;'>";
        echo "<p style='margin-bottom: 20px; font-size: 18px;'>" . htmlspecialchars($_SESSION['question_data'][$index]['question']) . "</p>";
        
        if ($_SESSION['question_data'][$index]['userans'] == 'none') {
            if ($_SESSION['question_data'][$index]['type'] == 'Objective') {
                echo "<input type='radio' name='option' value='" . $_SESSION['question_data'][$index]['optionA'] . "'>" . $_SESSION['question_data'][$index]['optionA'] . "<br>";
                echo "<input type='radio' name='option' value='" . $_SESSION['question_data'][$index]['optionB'] . "'>" . $_SESSION['question_data'][$index]['optionB'] . "<br>";
                echo "<input type='radio' name='option' value='" . $_SESSION['question_data'][$index]['optionC'] . "'>" . $_SESSION['question_data'][$index]['optionC'] . "<br>";
                echo "<input type='radio' name='option' value='" . $_SESSION['question_data'][$index]['optionD'] . "'>" . $_SESSION['question_data'][$index]['optionD'] . "<br>";
            } elseif ($_SESSION['question_data'][$index]['type'] == 'TF') {
                echo "<input type='radio' name='TF' value='TRUE'> TRUE<br>";
                echo "<input type='radio' name='TF' value='FALSE'> FALSE<br>";
            } else {
                echo "<input type='text' name='fill' value='' style='width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 5px;'>";
            }
        } else {
            if ($_SESSION['question_data'][$index]['type'] == 'Objective') {
                // Fetch the default answer if set
                $defaultAnswer = isset($_SESSION['question_data'][$index]['userans']) ? $_SESSION['question_data'][$index]['userans'] : '';
            
                // Generate radio buttons with default selection
                echo "<br><input type='radio' name='option' value='" . $_SESSION['question_data'][$index]['optionA'] . "' " . 
                     (($defaultAnswer == $_SESSION['question_data'][$index]['optionA']) ? 'checked' : '') . 
                     ">" . $_SESSION['question_data'][$index]['optionA'] . "<br>";
            
                echo "<input type='radio' name='option' value='" . $_SESSION['question_data'][$index]['optionB'] . "' " . 
                     (($defaultAnswer == $_SESSION['question_data'][$index]['optionB']) ? 'checked' : '') . 
                     ">" . $_SESSION['question_data'][$index]['optionB'] . "<br>";
            
                echo "<input type='radio' name='option' value='" . $_SESSION['question_data'][$index]['optionC'] . "' " . 
                     (($defaultAnswer == $_SESSION['question_data'][$index]['optionC']) ? 'checked' : '') . 
                     ">" . $_SESSION['question_data'][$index]['optionC'] . "<br>";
            
                echo "<input type='radio' name='option' value='" . $_SESSION['question_data'][$index]['optionD'] . "' " . 
                     (($defaultAnswer == $_SESSION['question_data'][$index]['optionD']) ? 'checked' : '') . 
                     ">" . $_SESSION['question_data'][$index]['optionD'] . "<br>";
            } elseif ($_SESSION['question_data'][$index]['type'] == 'TF') {
                // Fetch the default answer if set
                $defaultAnswer = isset($_SESSION['question_data'][$index]['userans']) ? $_SESSION['question_data'][$index]['userans'] : '';
            
                // Generate TRUE/FALSE radio buttons with default selection
                echo "<br><input type='radio' name='TF' value='TRUE' " . 
                     (($defaultAnswer == 'TRUE') ? 'checked' : '') . 
                     ">TRUE<br>";
            
                echo "<input type='radio' name='TF' value='FALSE' " . 
                     (($defaultAnswer == 'FALSE') ? 'checked' : '') . 
                     ">FALSE<br>";
            } else {
                // Fetch the default text answer if set
                $defaultAnswer = isset($_SESSION['question_data'][$index]['userans']) ? $_SESSION['question_data'][$index]['userans'] : '';
            
                // Generate text input with default value
                echo "<br><input type='text' name='fill' value='" . htmlspecialchars($defaultAnswer) . "'>";
            }
        }
        echo "</div>";
    }
    
    echo "</div>";
?>

        <button type="submit" name="submit" id="submit" style="display:none;">Submit</button>
        
        <?php
            echo "<footer class='navigation-footer'>";
            if ($_SESSION['question_index'] > 0) {
                echo "<button name='prev' id='prev' class='footer-button' style='padding: 10px 20px; border: none; border-radius: 5px; background-color: #4CAF50; color: white; cursor: pointer;'>◀️ Previous</button>";
            }

            if ($_SESSION['question_index'] == $_SESSION['total_question'] - 1) {
                echo "<button type='submit' name='submit' id='submit' class='footer-button' style='padding: 10px 20px; border: none; border-radius: 5px; background-color: #FF5722; color: white; cursor: pointer;'>Submit</button>";
            } else if (!($index == -1)) {
                echo "<button name='next' id='next' class='footer-button' style='padding: 10px 20px; border: none; border-radius: 5px; background-color: #4CAF50; color: white; cursor: pointer;'>NEXT ▶️</button>";
            }
            echo "</footer>";
        ?>


    </form>
</body>
</html>
