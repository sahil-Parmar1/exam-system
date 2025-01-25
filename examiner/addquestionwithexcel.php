<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();
if(isset($_GET['exam_id']) && isset($_GET['total_question']))
{
    $_SESSION['exam_id']=$_GET['exam_id'];
    $_SESSION['total_question']=$_GET['total_question'];
}

if (!isset($_SESSION['examinerusername']) || !isset($_SESSION['examinercourse'])) {
    // Redirect to login page
    header("Location: examiner_login.php");
    exit;
}
if(!isset($_SESSION['exam_id']) && !isset($_SESSION['total_question']))
{
    header("Location: examiner_dashboard.php");
    exit;
}
$exam_id=$_SESSION['exam_id'];
$total_question=$_SESSION['total_question'];

// Handle download demo file
if (isset($_GET['download_demo'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set headers
    $sheet->setCellValue('A1', 'No')
        ->setCellValue('B1', 'Type')
        ->setCellValue('C1', 'Question_Option')
        ->setCellValue('D1', 'Ans')
        ->setCellValue('E1', 'Question');

    // Add demo data
    $sheet->fromArray([
        [1, 'Objective', 'Delhi,,pune,,mumbai,,surat', 'Delhi', 'What is the capital of India?'],
        [2, 'TF', '-', 'TRUE', 'India is a country.'],
        [3, 'Fill', '-', '1947', 'India gained independence in?']
    ], null, 'A2');

    // Download as Excel file
    $writer = new Xlsx($spreadsheet);
    $demoFilePath = 'demo_file.xlsx';
    $writer->save($demoFilePath);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="demo_file.xlsx"');
    readfile($demoFilePath);
    unlink($demoFilePath);
    exit;
}

// Handle file upload and preview
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    $fileTmpPath = $_FILES['excel_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($fileTmpPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rowCount = $sheet->getHighestRow(); // Get the total number of rows
            $columnCount = $sheet->getHighestColumn(); // Get the highest column (A, B, C,...)

            // Check if there are 5 columns
            $columnCount = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($columnCount); // Convert column letter to number
            if ($rowCount < $total_question + 1) { // +1 to account for the header row
                echo "<script>alert('The uploaded file contains fewer questions than the required total. minimum ".$total_question." question');</script>";
                
            }
            else if ($columnCount === 5) {
                 $data = $sheet->toArray();
                    $_SESSION['uploaded_data'] = $data;

                    header('Location: ' . $_SERVER['PHP_SELF']);

            } else {
               echo "<script>alert('Please upload a valid file!');</script>";
            }

               
      
        
    } catch (Exception $e) {
        echo "<script>alert('"."Please upload valid file " . $e->getMessage()."');</script>";
    }
}

// Handle final submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ( isset($_POST['submit_data']) && isset($_SESSION['uploaded_data'])) {
    $uploadedData = $_SESSION['uploaded_data'];

     
    
         $servername = "localhost";
         $username = "root";
         $password = "";
         $dbname = $_SESSION['examinercourse'];
         $conn = new mysqli($servername, $username, $password, $dbname);
         if ($conn->connect_error) {
             die("Connection failed: " . $conn->connect_error);
         }
         $tablename=$exam_id."_exam";
            $sql = "CREATE TABLE $tablename (`id` INT(25) NOT NULL AUTO_INCREMENT , `type` VARCHAR(25) NOT NULL , `question_option` VARCHAR(100) NOT NULL , `ans` VARCHAR(100) NOT NULL , `question` VARCHAR(200) NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM";
            if ($conn->query($sql) === TRUE) {
             //insert the data into the table
                $j=1;
               for($i=1;$i<count($uploadedData);$i++)
               {
                   $type=$uploadedData[$i][1];
                   $question_option=$uploadedData[$i][2];
                   $ans=$uploadedData[$i][3];
                   $question=$uploadedData[$i][4];
                   $sql = "INSERT INTO $tablename (type, question_option, ans, question)
                   VALUES ('$type', '$question_option', '$ans', '$question')";
                   if($conn->query($sql) === TRUE)
                   {
                       $j=$j*1;
                   }
                   else
                   {
                        $j=0;
                   }
                  
               }
               if($j!=0)
               {
                   echo "<script>alert('Data inserted successfully');</script>";
                  header("Location: manage_exam.php");
                  exit;
               }
                else
                {
                     echo "<script>alert('something went wrong try again');</script>";
                }
           
            } else {
                echo "<script>alert('something went wrong try again');</script>";
            }
        
         $conn->close();
     
        // Insert data into the database
        // Redirect to the manage_exam.php page
        header("Location: manage_exam.php");
        exit;
     


    // Process each question
    
        unset($_SESSION['uploaded_data']);
    } 
    if (isset($_POST['cancel'])) {
        unset($_SESSION['uploaded_data']);
    }
   
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excel File Upload</title>
    <link rel="stylesheet" href="style/addquesitonwithexcel.css">
    <style>


/* Process Container */
.process-container {
    display: flex;
    align-items: center;
    justify-content: center; /* Centers the circles horizontally */
    margin-bottom: 20px;
    gap: 15px; /* Adds space between steps and connectors */
}

.step {
    width: 50px; /* Reduced size for better alignment */
    height: 50px;
    border-radius: 50%;
    display: flex;
    padding: 25px;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    font-weight: bold;
    text-align: center;
    color: white;
}

.step-blue {
    background: #007bff;
}

.step-gray {
    background: #007bff;
}

.connector {
    flex: 0 0 50px; /* Shortens the connector */
    height: 5px;
    background: #007bff;
    margin: 0; /* Removes extra margin */
}
    </style>
</head>
<body>
<br>
<div class="process-container">
    <div class="step step-blue">1<br>Exam Information</div>
    <div class="connector"></div>
    <div class="step step-gray">2<br>Upload Excel File</div>
</div>
    <h2>Excel File Upload Instructions</h2>
    <div class="instructions">
        <p>Please follow the given syntax for creating your Excel file:</p>
        <table>
            <tr>
                <th>No</th>
                <th>Type</th>
                <th>Question_Option</th>
                <th>Ans</th>
                <th>Question</th>
            </tr>
            <tr>
                <td>1</td>
                <td>Objective</td>
                <td>option A,,option B,,option C,,option D</td>
                <td>option A</td>
                <td>What is the capital of India?</td>
            </tr>
            <tr>
                <td>2</td>
                <td>TF</td>
                <td>-</td>
                <td>TRUE</td>
                <td>India is a country.</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Fill</td>
                <td>-</td>
                <td>1947</td>
                <td>India gained independence in?</td>
            </tr>
        </table>
        <p><strong>Note:</strong></p>
        <ul>
            <li>There are three types of questions: <b>Objective</b>, <b>TF</b> (True/False), and <b>Fill (fill in blank)</b>.</li>
            <li>For <b>Objective</b> questions, provide four options separated by <code><b>,,</b></code>.</li>
            <li>For <b>TF</b> or <b>Fill</b>, use <code><b>-</b></code> as an empty indicator for the options.</li>
            <li>You can add more than <b><?php echo htmlspecialchars($total_question)?></b> as Extra questions</li>
            <li><a href="?download_demo=true">
        <button>Download Demo File</button></li>
        </a>
        </ul>
        
    </div>

    

    <?php if (!isset($_SESSION['uploaded_data'])): ?>
        <h2>Upload Your Excel File</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="excel_file" accept=".xls,.xlsx" required>
            <button type="submit">Upload and Preview</button>
        </form>
    <?php else: ?>
        <h2>Preview of Uploaded File</h2>
        <table>
            <?php foreach ($_SESSION['uploaded_data'] as $row): ?>
                <tr>
                    <?php foreach ($row as $cell): ?>
                        <td><?php echo htmlspecialchars($cell); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
        <form action="" method="post">
            <button type="submit" name="cancel">Cancel</button>
            <button type="submit" name="submit_data">Submit Data</button>
        </form>
    <?php endif; ?>
</body>
</html>
