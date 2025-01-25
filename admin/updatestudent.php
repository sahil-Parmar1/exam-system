<?php
session_start();

// Check if user is not logged in (session variables not set)
if (!isset($_SESSION['adminusername']) || !isset($_SESSION['admincourse'])) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$student_id = intval($_GET['id']);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = $_SESSION['admincourse'];

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch student details
$sql = "SELECT * FROM student_info WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    echo "<script>alert('No student found with the given ID.');</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $roll = filter_var($_POST['roll'], FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $semester = filter_var($_POST['semester'], FILTER_VALIDATE_INT);

    if ($roll === false || $semester === false) {
        echo "<script>alert('Invalid roll or semester.');</script>";
        exit;
    }

    $admin_username = $_SESSION['username'];

    // Fetch admin semesters
    $admin_sql = "SELECT semesters FROM admin_info WHERE username = ?";
    $admin_stmt = $conn->prepare($admin_sql);
    $admin_stmt->bind_param("s", $admin_username);
    $admin_stmt->execute();
    $admin_result = $admin_stmt->get_result();

    if ($admin_result->num_rows > 0) {
        $admin = $admin_result->fetch_assoc();
        $total_semester = $admin['semesters'];
    } else {
        echo "<script>alert('Admin information not found.');</script>";
        exit;
    }
    $admin_stmt->close();

    if ($semester <= $total_semester) {
        $update_sql = "UPDATE student_info SET roll = ?, name = ?, username = ?, semester = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("issii", $roll, $name, $username, $semester, $student_id);

        if ($update_stmt->execute()) {
            echo "<script>
                alert('Student information updated successfully.');
                window.location.href = 'admin_dashboard.php';
            </script>";
            exit;
        } else {
            echo "<script>alert('Error updating student information: " . $conn->error . "');</script>";
        }
        $update_stmt->close();
    } else {
        echo "<script>alert('Semester exceeds the allowed limit.');</script>";
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
    <title>Update Student</title>
    <link rel="stylesheet" href="style/admin_login.css">
      <style>
         .back-button {
            display: inline-block;
            position: fixed;
            top: 10px;
            left: 10px;
            text-decoration: none;
            color: white;
            background-color: #4CAF50;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #45a049;
        }
      </style>
</head>
<body>
<a href="admin_dashboard.php" class="back-button">&larr;</a>
<div class="container">

    <h1>Update Student Information</h1>
    <div class="form-container">
    <form action="updatestudent.php" method="POST">
       

        <div class="mb-3">
        <label for="roll">Roll Number:</label>
        <input type="number" id="roll" name="roll" value="<?php echo htmlspecialchars($student['roll']); ?>" required><br>
        </div>
        <div class="mb-3">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required><br>
        </div>

        <div class="mb-3">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($student['username']); ?>" required><br>
        </div>

        <div class="mb-3">
        <label for="semester">Semester:</label>
        <input type="number" id="semester" name="semester" value="<?php echo htmlspecialchars($student['semester']); ?>" required><br>
        </div><br>
        <div class="d-grid">
       
                
                <button type="submit" class="login-button" id="update" name="update">Update</button>
            </div>
    </div>
    </form>
</div>
</body>
</html>
