<?php
session_start();
$message="";
$error="";
// Check if user is not logged in (session variables not set)
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
    $sql="CREATE TABLE subjects (id INT AUTO_INCREMENT PRIMARY KEY,subject_name VARCHAR(255) NOT NULL,subject_code VARCHAR(100) NOT NULL)";
    $conn->query($sql);
}
$sql = "SELECT id,subject_name, subject_code FROM subjects";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_subject'])) {
        $subject_name = trim($_POST['subject_name']);
        $subject_code = trim($_POST['subject_code']);

        // Validate subject name and code
        if (!empty($subject_name) && preg_match('/^[a-zA-Z ]+$/', $subject_name) && !empty($subject_code)) {
            $stmt = $conn->prepare("INSERT INTO subjects (subject_name, subject_code) VALUES (?, ?)");
            $stmt->bind_param("ss", $subject_name, $subject_code);

            if ($stmt->execute()) {
                $message = "Subject added successfully!";
            } else {
                $error = "Error adding subject.";
            }

            $stmt->close();
        } else {
            $error = "Invalid input. Subject name must contain only letters and spaces, and subject code is required.";
        }
    }

    if (isset($_POST['remove_subject'])) {
        $subject_id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->bind_param("i", $subject_id);

        if ($stmt->execute()) {
            $message = "Subject removed successfully!";
        } else {
            $error = "Error removing subject.";
        }

        $stmt->close();
    }

    if(isset($_POST['update']))
    {
        $subject_id = $_POST['subject_id'];
        $subject_name = trim($_POST['subject_name']);
        $subject_code = trim($_POST['subject_code']);

         if (!empty($subject_name) && preg_match('/^[a-zA-Z ]+$/', $subject_name) && !empty($subject_code)) {
            $stmt = $conn->prepare("UPDATE `subjects` SET `subject_code` = ?,`subject_name`=? WHERE id = ?");
            $stmt->bind_param("ssi", $subject_code,$subject_name,$subject_id);

            if ($stmt->execute()) {
                $message = "Subject updated successfully!";
            } else {
                $error = "Error adding subject.";
            }

            $stmt->close();
        } else {
            $error = "Invalid input no id is available";
        }

    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add or Remove Subject</title>
    <link rel="stylesheet" href="style/adremovesubject.css">
</head>
<body>
    
        <a href="admin_dashboard.php" class="back-button">&larr;
        </a>
    
    <div class="container">
        <h2>Manage Subjects</h2>

        <?php if (isset($message)) { echo "<div class='message'>$message</div>"; } ?>
        <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>

        <div class="subject-list">
            <h3>Subjects</h3>
            <ul>
                <?php foreach ($subjects as $subject) { ?>
                    <li>
                        <?php echo htmlspecialchars($subject['id'])." ".htmlspecialchars($subject['subject_name']) . " (" . htmlspecialchars($subject['subject_code']) . ")"; ?>
                        <div class="actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $subject['id']; ?>">
                                <button type="submit" name="remove_subject">Remove</button>
                            </form>
                           
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <form method="POST" style="margin-top: 20px;">
            <div class="form-group">
                <label for="subject_name">Subject Name</label>
                <input type="text" id="subject_name" name="subject_name" placeholder="Enter subject name" required>
            </div>

            <div class="form-group">
                <label for="subject_code">Subject Code</label>
                <input type="text" id="subject_code" name="subject_code" placeholder="Enter subject code" required>
            </div>
            <div class="form-group">
                <label for="subject_id">Subject id[optional only for update the subject]</label>
                <input type="text" id="subject_id" name="subject_id" placeholder="Enter subject code">
            </div>
            <button type="submit" name="add_subject" class="btn">Add Subject</button><br><br>
            <button class="btn" name="update">Update</button>
            <h2><?php echo $message?></h2>
        </form>
    </div>
</body>
</html>
