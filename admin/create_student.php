<?php
session_start();

// Check if user is not logged in (session variables not set)


// Check if user is not logged in (session variables not set)
if (!isset($_SESSION['adminusername']) || !isset($_SESSION['admincourse'])) {
    // Redirect to login page
    header("Location: admin_login.php");
    exit;
}

$servername = "localhost";
$dbuser = "root";
$dbpassword = "";
$course=$_SESSION['admincourse'];
$examinerusername=$_SESSION['adminusername'];
$semesters='';
// Create database connection
$conn = new mysqli($servername, $dbuser, $dbpassword);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
    $conn->select_db($course);
    $sql = "SELECT * FROM `admin_info` WHERE `username` LIKE '$examinerusername'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        // Fetch the row
        $row = $result->fetch_assoc();
        $semesters=$row['semesters'];
    }
    if($_SERVER['REQUEST_METHOD']=='POST')
    {
       if(isset($_POST['create']))
       {
        $password = $_POST['password'];
        $repassword = $_POST['repassword'];
        if ($password !== $repassword) {
            echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
            exit;
        }
        $checkTableSql = "SHOW TABLES LIKE 'student_info'";
        $result = $conn->query($checkTableSql);

        if ($result && $result->num_rows > 0) {
           //insert the data
        $semester = $_POST['semester'];
        $name = $_POST['name'];
        $roll = $_POST['roll'];
        $username = $_POST['username'];
        $checkUsernameQuery = "SELECT * FROM `student_info` WHERE `username` = '$username'";
        $usernameResult = $conn->query($checkUsernameQuery);
        if ($usernameResult->num_rows > 0) {
            echo "<script>alert('Username $username is already taken.');window.history.back();</script>";
            exit;
        }
        if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{4,}$/', $_POST['password'])) {
            echo "<script>alert('Password for username $username must contain at least one letter, one number, and one special character and atleast 4 charecter long');window.history.back();</script>";
            exit;
        }
        $password = password_hash($_POST['password'],PASSWORD_DEFAULT);
  
         
        $sql = "INSERT INTO student_info (semester, name, roll, username, password) VALUES ('$semester', '$name', '$roll', '$username', '$password')";
        $result = $conn->query($sql);

        if ($result) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var alertBox = document.createElement('div');
                        alertBox.className = 'alert alert-success';
                        alertBox.role = 'alert';
                        alertBox.innerHTML = 'Student registered successfully.';
                        document.body.prepend(alertBox);
                        setTimeout(function() {
                            alertBox.remove();
                        }, 3000);
                    });
                  </script>";
        } else {
            echo "<script>alert('Error registering student.');</script>";
        }
           
            
        } else {
            //create and insert the data
            $createTableSql = "CREATE TABLE student_info (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                semester INT(6) NOT NULL,
                name VARCHAR(100) NOT NULL,
                roll INT(6) NOT NULL,
                username VARCHAR(100) NOT NULL,
                password VARCHAR(100) NOT NULL
            )";
            $result=$conn->query($createTableSql);
            if($result)
            {
                $semester=$_POST['semester'];
                $name=$_POST['name'];
                $roll=$_POST['roll'];
                $username=$_POST['username'];
                $password = password_hash($_POST['password'],PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO student_info (semester, name, roll, username, password) VALUES ('$semester', '$name', '$roll', '$username', '$password')";
                $result = $conn->query($sql);
                if ($result) {
                    echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var alertBox = document.createElement('div');
                        alertBox.className = 'alert alert-success';
                        alertBox.role = 'alert';
                        alertBox.innerHTML = 'Student registered successfully.';
                        document.body.prepend(alertBox);
                        setTimeout(function() {
                            alertBox.remove();
                        }, 3000);
                    });
                  </script>";
                } else {
                    echo "<script>alert('Error registering student.');</script>";
                }
            }
            else
            {
                echo "<script>alert('Error creating table.');</script>";
            }
        }
       }
    }
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/admin_login.css">
</head>
<body>
    <div class="container">
    <form action="create_student.php" method="POST">
    <div class="form-container">
        <h2>Student Register</h2>
        <form id="registrationForm" action="process_form.php" method="POST">
            <div class="mb-3">
                <label for="semester" class="form-label">Semester</label>
                <select id="semester" name="semester" class="form-select" required>
                    <!-- Populate semesters dynamically using PHP -->
                    <?php
                    for ($i = 1; $i <= $semesters; $i++) {
                        echo "<option value='$i'>Semester $i</option>";
                    }
                    ?>
                </select>
            </div>
          
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="roll" class="form-label">Roll No</label>
                <input type="number" id="roll" name="roll" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" pattern="[a-zA-Z0-9]+@[0-9]+" required>
                <div class="form-text">Format: name@rollno</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control"  minlength="4" required>
            </div>
            <div class="mb-3">
                <label for="repassword" class="form-label">Re-Enter Password</label>
                <input type="password" id="repassword" name="repassword" class="form-control" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary" name="create">Submit</button>
            </div>
        </form>
    </div>

   
    </form>
</body>
</html>
