<?php
session_start();

// Check if user is not logged in (session variables not set)
if (!isset($_SESSION['studentusername']) || !isset($_SESSION['studentcourse'])) {
    // Redirect to login page
    header("Location: student_login.php");
    exit;
}
if(!isset($_GET['studentusername']))
{
    header("Location: student_dashboard.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = $_SESSION['studentcourse'];
$examinerusername=$_GET['studentusername'];
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch admin info
$sql = "SELECT * FROM student_info WHERE username='$examinerusername'";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Info</title>
    <link rel="stylesheet" href="../admin/style/my_profile.css">
</head>
<body>
<button class="back-btn" onclick="window.history.back()">&larr;</button> <!-- Back Button -->
<div class="container">
    <img src="../images/studentlogo.webp" alt="student Logo" class="admin-logo"> <!-- Admin Logo -->
    <h1>student Information</h1>

    <?php
    if ($result->num_rows > 0) {
        // Output data of each row
        while($row = $result->fetch_assoc()) {
            echo "<div class='admin-info'>";
            echo "<div><strong>ID:</strong> " . $row["id"] . "</div>";
            echo "<div><strong>Username:</strong> " . $row["username"] . "</div>";
            echo "<div><strong>Name</strong> " . $row["name"] . "</div>";
            echo "<div><strong>Roll</strong> " . $row["roll"] . "</div>";
            echo "<div><strong>semester</strong> " . $row["semester"] . "</div>";
            echo "<div><strong>course</strong> " . $_SESSION['studentcourse'] . "</div>";
            echo "</div>";

            echo "<div class='btn-container'>";
            echo "<button onclick=\"window.location.href='../change_password.php?id=".$row['id']."&type=student&course=".$_SESSION['studentcourse']."'\">Change Password</button>";
            echo "</div>";
        }
    } else {
        echo "<p>No student information found.</p>";
    }
    ?>

</div>

<?php
$conn->close();
?>

</body>
</html>
