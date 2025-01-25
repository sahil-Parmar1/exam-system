<?php
session_start();
// Check if user is not logged in
if (!(isset($_SESSION['adminusername']) && isset($_SESSION['admincourse']))) {
    header("Location: admin_login.php");
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', $_SESSION['admincourse']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch subjects from the database
$subjects = [];
// Check if the table 'subjects' exists
$tableExists = $conn->query("SHOW TABLES LIKE 'subjects'");
if ($tableExists->num_rows == 0) {
    // Create the table if it does not exist
    $_SESSION['error'] = "You need to add subjects before creating an examiner.";
    header("Location: adremovesubject.php");
    exit;
}
$sql = "SELECT id, subject_name, subject_code FROM subjects";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $subject_id = $_POST['subject'];
        $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
        $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
        $userpassword = $_POST['password'];
        $password=password_hash($userpassword,PASSWORD_DEFAULT);

        // Find selected subject details
        $subject_name = '';
        $subject_code = '';
        foreach ($subjects as $subject) {
            if ($subject['id'] == $subject_id) {
                $subject_name = $subject['subject_name'];
                $subject_code = $subject['subject_code'];
                break;
            }
        }

        // Ensure table exists
        $sql = "CREATE TABLE IF NOT EXISTS `examiner` (
            `examiner_id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `username` VARCHAR(255) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `subject_id` INT NOT NULL,
            `subject_name` VARCHAR(255) NOT NULL,
            `subject_code` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`examiner_id`)
        ) ENGINE = MyISAM";
        $conn->query($sql);

        // Insert data into the examiner table
        $sql = "INSERT INTO `examiner` (`name`, `username`, `password`, `subject_id`, `subject_name`, `subject_code`)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiss", $name, $username, $password, $subject_id, $subject_name, $subject_code);

        if ($stmt->execute()) {
            $_SESSION['success'] = "$name was added successfully.";
        } else {
            $_SESSION['error'] = "Failed to add $name. Please try again.";
        }

        header("Location: create_examiner.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Examiner</title>
    <link rel="stylesheet" href="style/admin_login.css">
    <script>
        function validateForm() {
            const name = document.getElementById('name').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const rePassword = document.getElementById('re_password').value;
            const subject = document.getElementById('subject').value;

            let isValid = true;

            // Validate name
            if (!/^[a-zA-Z ]+$/.test(name)) {
                document.getElementById('nameError').innerText = 'Name must contain only letters and spaces.';
                isValid = false;
            } else {
                document.getElementById('nameError').innerText = '';
            }

            // Validate username
            if (!/^[a-zA-Z]+@[0-9]+$/.test(username)) {
                document.getElementById('usernameError').innerText = 'Username must be in the format "xyz@123".';
                isValid = false;
            } else {
                document.getElementById('usernameError').innerText = '';
            }

            // Validate password
            if (!/(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&]).{6,}/.test(password)) {
                document.getElementById('passwordError').innerText = 'Password must contain at least one letter, one digit, and one special symbol and 6 charecter long';
                isValid = false;
            } else {
                document.getElementById('passwordError').innerText = '';
            }

            // Validate re-password
            if (password !== rePassword) {
                document.getElementById('rePasswordError').innerText = 'Passwords do not match.';
                isValid = false;
            } else {
                document.getElementById('rePasswordError').innerText = '';
            }

            // Validate subject
            if (subject.trim() === '') {
                document.getElementById('subjectError').innerText = 'Subject is required.';
                isValid = false;
            } else {
                document.getElementById('subjectError').innerText = '';
            }

            return isValid;
        }
    </script>
</head>
<body>

    <div class="container">
<div class="form-container">
    <h2>Create Teacher</h2><br>
    <?php
    if (isset($_SESSION['success'])) {
        echo "<div class='message'>{$_SESSION['success']}</div>";
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo "<div class='error-message'>{$_SESSION['error']}</div>";
        unset($_SESSION['error']);
    }
    ?>
    <form onsubmit="return validateForm();" method="POST" action="create_examiner.php">
        <div class="form-group">
            <label for="subject">Which subject is taught by the Teacher?</label>
            <select name="subject" id="subject" required>
                <option value="">--Select Subject--</option>
                <?php
                foreach ($subjects as $row) {
                    echo "<option value='".htmlspecialchars($row['id'])."'>"
                        .htmlspecialchars($row['subject_name'])." (".htmlspecialchars($row['subject_code']).")</option>";
                }
                ?>
            </select>
            <div class='error-message' id="subjectError"></div>
        </div>
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>
            <div class='error-message' id="nameError"></div>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
            <div class='error-message' id="usernameError"></div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <div class='error-message' id="passwordError"></div>
        </div>

        <div class="form-group">
            <label for="re_password">Re-enter Password</label>
            <input type="password" id="re_password" name="re_password" required>
            <div class='error-message' id="rePasswordError"></div>
        </div>
        <div class="form-group">
      <button type="submit" name="submit" id="submit" class="login-button">Ragister</button>
    </div>
    </form>
    <div class="form-group">
      <button onclick="window.location.href='admin_dashboard.php'" class="bkbutton" >Back to Home</button>
    </div>
</div>
</body>
</html>
