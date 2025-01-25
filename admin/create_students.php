<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();


if (!isset($_SESSION['adminusername']) || !isset($_SESSION['admincourse'])) {
    // Redirect to login page
    header("Location: admin_login.php");
    exit;
}



// Handle download demo file
if (isset($_GET['download_demo'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set headers
    $sheet->setCellValue('A1', 'id')
        ->setCellValue('B1', 'roll')
        ->setCellValue('C1', 'name')
        ->setCellValue('D1', 'username')
        ->setCellValue('E1', 'password')
        ->setCellValue('F1', 'semester');

    // Add demo data
    $sheet->fromArray([
        [1, 78, 'Parmar sahil', 'ty@78','sahil@78',6],
        [2, 79, 'Parmar Yash', 'ty@79','yash@79',6],
        [3, 90, 'Patel Adil', 'ty@80','adil@80',6],
    ], null, 'A2');

    // Download as Excel file
    $writer = new Xlsx($spreadsheet);
    $demoFilePath = 'demo_file.xlsx';
    $writer->save($demoFilePath);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="demo_student_file.xlsx"');
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
             if ($columnCount === 6) {
                 $data = $sheet->toArray();
                 $username=$data[1][3];
                 $password=$data[1][4];
                if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{3,}$/', $username)) {
                    echo "<script>alert('Username must contain at least one letter, one number, and one special character.');</script>";
                   
                }
                else if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{3,}$/', $password)) {
                    echo "<script>alert('Password must contain at least one letter, one number, and one special character.');</script>";
                    
                }
                else
                {
                    $_SESSION['uploaded_data'] = $data;

                    header('Location: ' . $_SERVER['PHP_SELF']);  
                }
                  

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
         $dbname = $_SESSION['admincourse'];
         $conn = new mysqli($servername, $username, $password, $dbname);
         if ($conn->connect_error) {
             die("Connection failed: " . $conn->connect_error);
         }
       
        $checkTableQuery = "SHOW TABLES LIKE 'student_info'";
        $result = $conn->query($checkTableQuery);
         $resultcreate=false;
        if ($result->num_rows == 0) {
           $sql="CREATE TABLE `student_info` (`id` INT(200) NOT NULL AUTO_INCREMENT , `roll` INT(200) NOT NULL , `name` VARCHAR(25) NOT NULL , `username` VARCHAR(25) NOT NULL , `password` VARCHAR(25) NOT NULL , `semester` INT(25) NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM";
           $resultcreate=$conn->query($sql);
        } 
        else
        {
            $resultcreate=TRUE;
        }
        
            if ($resultcreate === TRUE) {
             //insert the data into the table
                $j=1;
               for($i=1;$i<count($uploadedData);$i++)
               {
                   $roll=$uploadedData[$i][1];
                   $name=$uploadedData[$i][2];
                   $username=$uploadedData[$i][3];
                   $checkUsernameQuery = "SELECT * FROM `student_info` WHERE `username` = '$username'";
                   $usernameResult = $conn->query($checkUsernameQuery);
                   if ($usernameResult->num_rows > 0) {
                       echo "<script>alert('Username $username is already taken.');window.history.back();</script>";
                       exit;
                   }
                if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{4,}$/', $uploadedData[$i][4])) {
                    echo "<script>alert('Password for username $username must contain at least one letter, one number, and one special character and atleast 4 charecter long');window.history.back();</script>";
                    exit;
                }
                   $password=password_hash($uploadedData[$i][4],PASSWORD_DEFAULT);
                     $semester=$uploadedData[$i][5];
                   $sql = "INSERT INTO `student_info` (roll, name, username, password, semester)
                   VALUES ('$roll', '$name', '$username', '$password', '$semester')";
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
                  header("Location: student_list.php");
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
    <link rel="stylesheet" href="../examiner/style/addquesitonwithexcel.css">
</head>
<body>

    <h2>Excel File Upload Instructions</h2>
    <div class="instructions">
        <p>Please follow the given syntax for creating your Excel file:</p>
        <table>
            <tr>
                <th>id</th>
                <th>roll</th>
                <th>name</th>
                <th>username</th>
                <th>password</th>
                <th>semester</th>
            </tr>
            <tr>
                <td>1</td>
                <td>78</td>
                <td>Parmar sahil</td>
                <td>ty@078</td>
                <td>sahil@78</td>
                <td>6</td>
            </tr>
            <tr>
                <td>2</td>
                <td>96</td>
                <td>Purohit jaimin</td>
                <td>ty@096</td>
                <td>jaimin@96</td>
                <td>6</td>
            </tr>
            <tr>
                <td>1</td>
                <td>118</td>
                <td>Ven prakash</td>
                <td>ty@118</td>
                <td>ven@118</td>
                <td>6</td>
            </tr>
        </table>
        <p><strong>Note:</strong></p>
        <ul>
         <li>Username must be unique</li>
           <li>you must me provide 6 colums (id,roll,name,username,password,semester) in order</li>
           <li>username must be in the form of student@123 format because it will not change</li>
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
