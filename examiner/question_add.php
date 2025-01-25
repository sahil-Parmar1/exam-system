<?php
session_start();
if(isset($_GET['exam_id']))
{
    $_SESSION['exam_id']=$_GET['exam_id'];
}
if (!isset($_SESSION['examinerusername']) || !isset($_SESSION['examinercourse'])) {
    // Redirect to login page
    header("Location: examiner_login.php");
    exit;
}
if(!isset($_SESSION['exam_id']))
{
    header("Location: examiner_dashboard.php");
    exit;
}
$exam_id=$_SESSION['exam_id'];


// Establish database connection
$conn = new mysqli('localhost', 'root', '', $_SESSION['examinercourse']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql="SELECT *FROM exams WHERE exam_id=$exam_id";
$result=$conn->query($sql);
$examname='';
$examiner=$_SESSION['examinerusername'];
$subject_id='';
$total_questions='';
$perquestion_mark='';
$total_marks='';
$timeofexam='';
$dateofexam='';
$timelimit='';
$negative_mark='';
$subject_name='';
$subject_code='';
$examtable='';
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $examname=$row['exam_name'];
    $examiner=$row['examinerusername'];
    $subject_id=$row['subject_id'];
    $total_questions=$row['total_question'];
    $perquestion_mark=$row['perquestion_mark'];
    $total_marks=$row['total_marks'];
    $timeofexam=$row['timeofexam'];
    $dateofexam=$row['dateofexam'];
    $timelimit=$row['timelimit'];
    $negative_mark=$row['negative_mark'];
    $examtable=$exam_id."_exam";
    $question_index=0;
    $sql="SELECT *FROM  subjects WHERE id=$subject_id";
    $result=$conn->query($sql);
    if($result && $result->num_rows>0)
    {
        $row=$result->fetch_assoc();
        $subject_name=$row['subject_name'];
        $subject_code=$row['subject_code'];
    }
} else {
    echo "No exam found with the name '$examname'.";
}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    // Handle the AJAX request
    if (!isset($_SESSION['exam_id'])) {
        echo json_encode(['error' => 'Exam ID not set']);
        exit;
    }

    $exam_id = $_SESSION['exam_id'];
    $conn = new mysqli('localhost', 'root', '', $_SESSION['examinercourse']);
    if ($conn->connect_error) {
        echo json_encode(['error' => $conn->connect_error]);
        exit;
    }

    $examtable = $exam_id . "_exam";
    $sql = "SELECT COUNT(*) as count FROM `$examtable`";
    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['count' => $row['count']]);
    } else {
        echo json_encode(['count' => 0]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>question add</title>
  <style>
     .success-message {
        background-color: #d4edda; /* Light green background */
        color: #155724; /* Dark green text */
        padding: 10px;
        border: 1px solid #c3e6cb; /* Border color */
        border-radius: 5px; /* Rounded corners */
        font-size: 16px;
        margin: 10px 0;
        text-align: center;
        font-weight: bold;
    }
    .dynamic-div {
      display: none;
      margin-top: 10px;
      padding: 10px;
      border: 1px solid #ccc;
      background-color: #f9f9f9;
      text-align:center;
    }
    table {
        width: 80%;
        margin: 20px auto;
        border-collapse: collapse;
        background-color: #fefefe;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        font-family: Arial, sans-serif;
    }

    th, td {
        padding: 12px 15px;
        border: 1px solid #ddd;
        text-align: left;
    }

    th {
        background-color: #f4f4f4;
        font-weight: bold;
        font-size: 16px;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    label {
        font-weight: bold;
    }

    .select {
        width: calc(100% - 10px);
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-top: 5px;
        box-sizing: border-box;
    }

    button {
        padding: 10px 20px;
        font-size: 14px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        background-color: #007BFF;
        color: white;
    }

    button:hover {
        background-color: #0056b3;
    }

    .actions button {
        margin-right: 10px;
    }

    .error {
        color: red;
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }
  </style>
  <script>
    async function updateQuestionCount() {
        try {
            const response = await fetch('', {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            if (data.error) {
                console.error('Error:', data.error);
            } else {
                const totalQuestions = <?php echo htmlspecialchars($total_questions); ?>;
                document.getElementById('question-count').innerText =
                    `Questions: ${data.count}/${totalQuestions}`;
            }
        } catch (error) {
            console.error('Fetch error:', error);
        }
    }

    // Call updateQuestionCount initially and set interval for live update
    updateQuestionCount();
    setInterval(updateQuestionCount, 5000); // Update every 5 seconds
</script>

</head>
<body>
    <form method="POST" action="question_add.php">

  <center><h1>Add Questions</h1>
 
  
  <h2><?php echo htmlspecialchars($subject_name)."(".htmlspecialchars($subject_code).")"?></h2>
  <h2><?php echo htmlspecialchars($examname)?></h2>
  <h3><?php echo "[".htmlspecialchars($dateofexam)." ".htmlspecialchars($timeofexam)." ]" ?></h3>
  <h2 id="question-count">Questions: 0/<?php echo htmlspecialchars($total_questions); ?></h2>

       <h6>*You can add more than <?php echo htmlspecialchars($total_questions);?> as extra Questions</h6>
       <?php
  
 
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
               
                if (isset($_POST['addobjective'])) {
                   
                    $examtable = mysqli_real_escape_string($conn, $examtable); // Ensure $examtable is sanitized
                    $checkTableSql = "SHOW TABLES LIKE '$examtable'";
                    $result = $conn->query($checkTableSql);
            
                    if ($result && $result->num_rows > 0) {
                       
                        insertData($conn, $examtable, $_POST);
                        
                    } else {
                        $createTableSql = "
                            CREATE TABLE `$examtable` (
                                `id` INT NOT NULL AUTO_INCREMENT,
                                `type` VARCHAR(25) NOT NULL,
                                `question_option` VARCHAR(5000) NOT NULL,
                                `ans` VARCHAR(5000) NOT NULL,
                                `question` VARCHAR(5000) NOT NULL,
                                PRIMARY KEY (`id`)
                            ) ENGINE = MyISAM
                        ";
                        if ($conn->query($createTableSql) === TRUE) {
                            
                            insertData($conn, $examtable, $_POST);
                           
                        } else {
                            echo "Error creating table: " . $conn->error;
                        }
                    }
                }

                if (isset($_POST['addtruefalse'])) {
                   
                    $examtable = mysqli_real_escape_string($conn, $examtable); // Ensure $examtable is sanitized
                    $checkTableSql = "SHOW TABLES LIKE '$examtable'";
                    $result = $conn->query($checkTableSql);
            
                    if ($result && $result->num_rows > 0) {
                       
                        insertDataTF($conn, $examtable, $_POST);
                        
                    } else {
                        $createTableSql = "
                            CREATE TABLE `$examtable` (
                                `id` INT NOT NULL AUTO_INCREMENT,
                                `type` VARCHAR(25) NOT NULL,
                                `question_option` VARCHAR(5000) NOT NULL,
                                `ans` VARCHAR(5000) NOT NULL,
                                `question` VARCHAR(5000) NOT NULL,
                                PRIMARY KEY (`id`)
                            ) ENGINE = MyISAM
                        ";
                        if ($conn->query($createTableSql) === TRUE) {
                            
                            insertDataTF($conn, $examtable, $_POST);
                           
                        } else {
                            echo "Error creating table: " . $conn->error;
                        }
                    }
                }
                if (isset($_POST['addFill'])) {
                   
                    $examtable = mysqli_real_escape_string($conn, $examtable); // Ensure $examtable is sanitized
                    $checkTableSql = "SHOW TABLES LIKE '$examtable'";
                    $result = $conn->query($checkTableSql);
            
                    if ($result && $result->num_rows > 0) {
                       
                        insertDataFILL($conn, $examtable, $_POST);
                        
                    } else {
                        $createTableSql = "
                            CREATE TABLE `$examtable` (
                                `id` INT NOT NULL AUTO_INCREMENT,
                                `type` VARCHAR(25) NOT NULL,
                                `question_option` VARCHAR(5000) NOT NULL,
                                `ans` VARCHAR(5000) NOT NULL,
                                `question` VARCHAR(5000) NOT NULL,
                                PRIMARY KEY (`id`)
                            ) ENGINE = MyISAM
                        ";
                        if ($conn->query($createTableSql) === TRUE) {
                            
                            insertDataFILL($conn, $examtable, $_POST);
                           
                        } else {
                            echo "Error creating table: " . $conn->error;
                        }
                    }
                }
            }
            
            function insertData($conn, $examtable, $postData) {
                $sql = "INSERT INTO `$examtable` (`type`, `question_option`, `ans`, `question`) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    echo "Error preparing statement: " . $conn->error;
                    return;
                }
            
                $type = "Objective";
                $question = $postData['question_textarea'];
                $optionA = $postData['optionA'];
                $optionB = $postData['optionB'];
                $optionC = $postData['optionC'];
                $optionD = $postData['optionD'];
                $answer = $postData['answer_select'];
                switch ($answer) {
                    case 'A':
                        $answer = $optionA;
                        break;
                    case 'B':
                        $answer = $optionB;
                        break;
                    case 'C':
                        $answer = $optionC;
                        break;
                    case 'D':
                        $answer = $optionD;
                        break;
                }
                $question_option = implode(",,", [$optionA, $optionB, $optionC, $optionD]);
            
                $stmt->bind_param("ssss", $type, $question_option, $answer, $question);
                if ($stmt->execute()) {
                    echo '<br><div class="success-message">1 Question Added Successfully</div><br>';
                } else {
                    echo "Error inserting data: " . $stmt->error;
                }
                $stmt->close();
            }
  
            function insertDataTF($conn, $examtable, $postData) {
                $sql = "INSERT INTO `$examtable` (`type`, `question_option`, `ans`, `question`) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    echo "Error preparing statement: " . $conn->error;
                    return;
                }
            
                $type = "TF";
                $question = $postData['question_textarea_true'];
                 $answer = $postData['answer_select_true'];
                $question_option = implode(",,", ["TRUE","FALSE"]);
            
                $stmt->bind_param("ssss", $type, $question_option, $answer, $question);
                if ($stmt->execute()) {
                    echo '<br><div class="success-message">1 Question Added Successfully</div><br>';
                } else {
                    echo "Error inserting data: " . $stmt->error;
                }
                $stmt->close();
            }
            function insertDataFILL($conn, $examtable, $postData) {
                $sql = "INSERT INTO `$examtable` (`type`, `question_option`, `ans`, `question`) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    echo "Error preparing statement: " . $conn->error;
                    return;
                }
            
                $type = "fill";
                $question = $postData['question_textarea_fill'];
                 $answer = strtoupper($postData['answer_text_fill']);
                $question_option = "-";
            
                $stmt->bind_param("ssss", $type, $question_option, $answer, $question);
                if ($stmt->execute()) {
                    echo '<br><div class="success-message">1 Question Added Successfully</div><br>';
                } else {
                    echo "Error inserting data: " . $stmt->error;
                }
                $stmt->close();
            }
       
       ?>
  <select id="dropdownMenu">
    <option value="" disabled selected>Select an Questions type:</option>
    <option value="d1">Objective</option>
    <option value="d2">TRUE or FALSE</option>
    <option value="d3">Fill in blank</option>
  </select></center>
 
  <div id="d1" class="dynamic-div">
        <h2 align="center">Objective Question</h2>
        <table align="center">
            <tr>
                <td><label for="question_textarea">Question:</label></td>
                <td><textarea id="question_textarea"  
                name="question_textarea"
                placeholder="Enter your question here..." class="select"></textarea><br>
                <div class="error" id="question_error"></div>
                 </td>
            </tr>
            <tr>
                <td><label>Options:</label></td>
                <td>
                    <table>
                        <tr>
                            <td>A <input type="text" id="optionA" 
                            name="optionA"
                            placeholder="Option A" class="select"><br>   <div class="error" id="optionA_error"></div></td>
                            <td>B <input type="text" id="optionB" 
                            name="optionB"
                            placeholder="Option B" class="select"><br>   <div class="error" id="optionB_error"></div></td>
                        </tr>
                        <tr>
                            <td>C <input type="text" id="optionC" 
                            name="optionC"
                            placeholder="Option C" class="select"><br>   <div class="error" id="optionC_error"></div></td>
                            <td>D <input type="text" id="optionD" 
                            name="optionD"
                            placeholder="Option D" class="select"><br>   <div class="error" id="optionD_error"></div></td>
                        </tr>
                        <tr>
                            <td>Answer:</td>
                            <td>
                                <select id="answer_select" 
                                name="answer_select"
                                class="select">
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="actions">
                            <td><button id="clear" onclick="clearFields()">Clear</button></td>
                            <td><button id="addobjective" type="submit" name="addobjective" onclick="validateObjectiveQuestionForm(event)">Add</button></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table> <script>
        // Function to clear the input fields
        function clearFields() {
            document.getElementById('question_textarea').value = '';
            document.getElementById('optionA').value = '';
            document.getElementById('optionB').value = '';
            document.getElementById('optionC').value = '';
            document.getElementById('optionD').value = '';
            document.getElementById('answer_select').selectedIndex = 0;
        }

        function validateObjectiveQuestionForm(event) {
        const question = document.getElementById('question_textarea').value.trim();
        const optionA = document.getElementById('optionA').value.trim();
        const optionB = document.getElementById('optionB').value.trim();
        const optionC = document.getElementById('optionC').value.trim();
        const optionD = document.getElementById('optionD').value.trim();
        const answer = document.getElementById('answer_select').value;

        let isValid = true;

        // Clear previous errors
        document.querySelectorAll('.error').forEach(el => el.textContent = '');

        // Validate Question
        if (question === "") {
            document.getElementById('question_error').textContent = "Question is required.";
            isValid = false;
        }

        // Validate Options
        if (optionA === "") {
            document.getElementById('optionA_error').textContent = "Option A is required.";
            isValid = false;
        }
        if (optionB === "") {
            document.getElementById('optionB_error').textContent = "Option B is required.";
            isValid = false;
        }
        if (optionC === "") {
            document.getElementById('optionC_error').textContent = "Option C is required.";
            isValid = false;
        }
        if (optionD === "") {
            document.getElementById('optionD_error').textContent = "Option D is required.";
            isValid = false;
        }

        // Validate Answer
        if (answer === "") {
            document.getElementById('answer_error').textContent = "You must select an answer.";
            isValid = false;
        }

        // Prevent form submission if validation fails
        if (!isValid) {
            event.preventDefault();
        }
    }
      // Function to clear the input fields
      function clearFields_true() {
            document.getElementById('question_textarea_true').value = '';
            document.getElementById('answer_select_true').selectedIndex = 0;
        }

        // Function to clear the input fields
      function clearFields_fill() {
            document.getElementById('question_textarea_fill').value = '';
            document.getElementById('answer_text_fill').value = '';
        }
        function validateObjectiveQuestionForm_true(event) {
        const question = document.getElementById('question_textarea_true').value.trim();
        const answer = document.getElementById('answer_select_true').value;

        let isValid = true;

        // Clear previous errors
        document.querySelectorAll('.error').forEach(el => el.textContent = '');

        // Validate Question
        if (question === "") {
            document.getElementById('question_error_true').textContent = "Question is required.";
            isValid = false;
        }
         // Validate Answer
        if (answer === "") {
            document.getElementById('answer_error_true').textContent = "You must select an answer.";
            isValid = false;
        }

        // Prevent form submission if validation fails
        if (!isValid) {
            event.preventDefault();
        }
    }
    function validateObjectiveQuestionForm_fill(event) {
        const question = document.getElementById('question_textarea_fill').value.trim();
        const answer = document.getElementById('answer_text_fill').value;

        let isValid = true;

        // Clear previous errors
        document.querySelectorAll('.error').forEach(el => el.textContent = '');

        // Validate Question
        if (question === "") {
            document.getElementById('question_error_fill').textContent = "Question is required.";
            isValid = false;
        }
         // Validate Answer
        if (answer === "") {
            document.getElementById('answer_error_fill').textContent = "You must provide an answer.";
            isValid = false;
        }

        // Prevent form submission if validation fails
        if (!isValid) {
            event.preventDefault();
        }
    }
    </script>
    </div>

   
  <div id="d2" class="dynamic-div">
  <h2 align="center">TRUE or FALSE Question</h2>
        <table align="center">
            <tr>
                <td><label for="question_textarea_true">Question:</label>
                <td><textarea id="question_textarea_true" name="question_textarea_true" placeholder="Enter your question here..." class="select"></textarea><br>
                <div class="error" id="question_error_true"></div>
                 </td>

            </tr>
            <tr>
                            <td>Answer:</td>
                            <td>
                                <select id="answer_select_true"  name="answer_select_true" class="select">
                                    <option value="TRUE">True</option>
                                    <option value="FALSE">False</option>
                                </select><br>
                                <div class="error" id="answer_error_true"></div>
                            </td>
                        </tr>
            
                
                        <tr class="actions">
                            <td><button id="clear" onclick="clearFields_true()">Clear</button></td>
                            <td><button id="addtruefalse" name="addtruefalse" type="submit" onclick="validateObjectiveQuestionForm_true(event)">Add</button></td>
                        </tr>
                 
           
        </table> 
  </div>
  <div id="d3" class="dynamic-div">
  <h2 align="center">Fill in Blank Question</h2>
        <table align="center">
            <tr>
                <td><label for="question_textarea_fill">Question:</label></td>
                <td><textarea id="question_textarea_fill" name="question_textarea_fill" placeholder="Enter your question here..." class="select"></textarea><br>
               
                <h5>[* use '____'(underscore) to represent the blank in question]</h5> <br>
                <div class="error" id="question_error_fill"></div>
                 </td>

            </tr>
            <tr>
                            <td>Answer:</td>
                            <td>
                                <input type="text" class="select" id="answer_text_fill" name="answer_text_fill" placeholder="Enter your Ans here"/>
                                <div class="error" id="answer_error_fill"></div>
                            </td>
                        </tr>
            
                
                        <tr class="actions">
                            <td><button id="clear" onclick="clearFields_fill()">Clear</button></td>
                            <td><button id="addFill" name="addFill" type="submit" onclick="validateObjectiveQuestionForm_fill(event)">Add</button></td>
                        </tr>
                 
           
        </table> 
  </div>

  <script>
    const dropdownMenu = document.getElementById("dropdownMenu");
    const divs = document.querySelectorAll(".dynamic-div");

    dropdownMenu.addEventListener("change", function () {
      const selectedValue = this.value;

      // Hide all divs
      divs.forEach((div) => {
        div.style.display = "none";
      });

      // Show the selected div
      const selectedDiv = document.getElementById(selectedValue);
      if (selectedDiv) {
        selectedDiv.style.display = "block";
      }
    });
  </script>
<footer style="background-color: #f4f4f4; padding: 20px 40px; margin-top: 20px; border-top: 1px solid #ddd; font-family: Arial, sans-serif; font-size: 14px; color: #333;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h3 style="margin-bottom: 10px; color: #007BFF;">Exam Details</h3>
        <p><strong>Subject:</strong> <?php echo htmlspecialchars($subject_name); ?></p>
        <p><strong>Subject Code:</strong> <?php echo htmlspecialchars($subject_code); ?></p>
        <p><strong>Examiner:</strong> <?php echo htmlspecialchars($examiner); ?></p>
    </div>
    <div style="margin-top: 20px; max-width: 1200px; margin: 0 auto;">
        <h3 style="margin-bottom: 10px; color: #007BFF;">Marking Scheme</h3>
        <p><strong>Total Marks:</strong> <?php echo htmlspecialchars($total_marks); ?></p>
        <p><strong>Per Question Mark:</strong> <?php echo htmlspecialchars($perquestion_mark); ?></p>
        <p><strong>Negative Mark:</strong> <?php 
        if($negative_mark != null)
        echo htmlspecialchars($negative_mark);
        else
        echo htmlspecialchars("None")
        ?></p>
    </div>
    <div style="margin-top: 20px; max-width: 1200px; margin: 0 auto;">
        <h3 style="margin-bottom: 10px; color: #007BFF;">Exam Timing</h3>
        <p><strong>Duration:</strong> <?php echo htmlspecialchars($timelimit); ?></p>
        <p><strong>Time:</strong> <?php echo htmlspecialchars($timeofexam); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($dateofexam); ?></p>
    </div>
    <div style="margin-top: 30px; text-align: center; color: #666;">
        <p>&copy; <?php echo date('Y'); ?> Exam Management System. All Rights Reserved.</p>
    </div>
</footer>


</form>
</body>
</html>
