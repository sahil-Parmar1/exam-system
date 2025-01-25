<?php
session_start();

// Check if user is not logged in (session variables not set)
if (!isset($_SESSION['adminusername']) || !isset($_SESSION['admincourse'])) {
    // Redirect to login page
    header("Location: admin_login.php");
    exit;
}

$username = $_SESSION['adminusername'];
$course =$_SESSION['admincourse'];

// Database connection
$servername = "localhost";
$dbusername = "root";
$password = "";
$dbname = $course;

$conn = new mysqli($servername, $dbusername, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Error handling variables
$errors = [];

// Handle form submission
if (isset($_POST['submit'])) {
    $new_semester = $_POST['new_semester'];

    // Validate the input (check if it's a valid number and positive)
    if (empty($new_semester) || !is_numeric($new_semester) || $new_semester <= 0) {
        $errors[] = "Please enter a valid semester number.";
    }

    // If no errors, proceed to update the database
    if (empty($errors)) {
        // Prepare the SQL query to update the semester
        $sql = "UPDATE admin_info SET semesters = ? WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $new_semester, $username); // "i" for integer, "s" for string

        if ($stmt->execute()) {
            echo "<p>Semester updated successfully!</p>";
        } else {
            $errors[] = "Failed to update semester. Please try again.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Semester</title>
    <link rel="stylesheet" href="../change_password.css">
    <style>
        /* Add your existing CSS or style below */
        .back-button {
            text-decoration: none;
            font-size: 20px;
            color: #333;
            padding: 10px;
            margin: 10px;
            display: inline-block;
        }

        .error {
            color: red;
            font-size: 14px;
        }

        .error p {
            margin: 5px 0;
        }

        form {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        form label {
            display: block;
            margin-bottom: 8px;
        }

        form input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        form button {
            padding: 10px;
            width: 100%;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<a href="javascript:history.back()" class="back-button">&larr; Back</a>

<h1>Change Semester</h1>

<?php if (!empty($errors)): ?>
    <div class="error">
        <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" action="">
    <label for="new_semester">Enter New Semester:</label>
    <input type="number" name="new_semester" id="new_semester" required>
    
    <button type="submit" name="submit">Change Semester</button>
</form>

</body>
</html>
