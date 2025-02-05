<?php
session_start();

// Check if required parameters are present
if (!isset($_GET['id'], $_GET['type'], $_GET['course'])) {
    echo "Invalid request";
    exit;
}

$type = $_GET['type'];
$id = $_GET['id'];
$course = $_GET['course'];
$table = '';
$usernameadmin = '';

// Determine the table and validate session
switch ($type) {
    case 'student':
        if (isset($_SESSION['adminusername']) || isset($_SESSION['studentusername'])) {
            $table = 'student_info';
        } else {
            echo "Invalid request";
            exit;
        }
        break;
    case 'admin':
        if (isset($_SESSION['adminusername'])) {
            $table = 'admin_info';
            $usernameadmin = $_SESSION['adminusername'];
        } else {
            echo "Invalid request";
            exit;
        }
        break;
    case 'examiner':
        if (isset($_SESSION['adminusername']) || isset($_SESSION['examinerusername'])) {
            $table = 'examiner';
        } else {
            echo "Invalid request";
            exit;
        }
        break;
    default:
        echo "Invalid request";
        exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['submit']))
    {
            $new_password = $_POST['new_password'] ?? '';
            $re_password = $_POST['re_password'] ?? '';
            $errors = [];

            // Validation
            if (empty($new_password) || empty($re_password)) {
                $errors[] = "Both fields are required.";
            } elseif ($new_password !== $re_password) {
                $errors[] = "Passwords do not match.";
            } elseif (!preg_match('/[A-Za-z]/', $new_password)) {
                $errors[] = "Password must contain at least one letter.";
            } elseif (!preg_match('/\d/', $new_password)) {
                $errors[] = "Password must contain at least one digit.";
            } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
                $errors[] = "Password must contain at least one special character.";
            }

            // If no errors, update the password
            if (empty($errors)) {
                // Database connection
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = $course;

                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Prepare the statement based on type
                if($table=="student_info")
                {
                    $newhash_password=$new_password;
                }
                else
                {
                   $newhash_password=password_hash($new_password,PASSWORD_DEFAULT); 
                }
                
                switch ($type) {
                    case 'student':
                        $stmt = $conn->prepare("UPDATE student_info SET password = ? WHERE id = ?");
                        $stmt->bind_param("si", $newhash_password, $id);
                        break;
                    case 'admin':
                    // echo "admin password changed<br>";
                    // echo "$usernameadmin";
                        $stmt = $conn->prepare("UPDATE admin_info SET password = ? WHERE username = ?");
                        $stmt->bind_param("ss", $newhash_password, $usernameadmin);
                        break;
                    case 'examiner':
                        $stmt = $conn->prepare("UPDATE examiner SET password = ? WHERE examiner_id = ?");
                        $stmt->bind_param("si", $newhash_password, $id);
                        break;
                    default:
                        echo "Invalid request";
                        exit;
                }

                // Execute the statement
                if ($stmt->execute()) {
                    echo "<script>
                    alert('Password changed successfully!');
                    
                </script>";
                } else {
                    echo "<p style='color: red;'>Error updating password. Please try again.</p>";
                    
                }

                $stmt->close();
                $conn->close();
                
            }
    }
  
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="change_password.css">

</head>
<body>
 <a href="javascript:history.back()" class="back-button">&larr;</a>
    <h1>Change Password</h1>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="">  
        
        <label for="new_password">Enter New Password:</label>
        <input type="password" name="new_password" id="new_password" required>
        
        <label for="re_password">Re-enter Password:</label>
        <input type="password" name="re_password" id="re_password" required>
        
        <button type="submit" name="submit">Change Password</button>
    </form>
</body>
</html>
