<?php
session_start();

// Check if user is not logged in (session variables not set)
if (!isset($_SESSION['examinerusername']) || !isset($_SESSION['examinercourse'])) {
    // Redirect to login page
    header("Location: examiner_login.php");
    exit;
}
if(!isset($_GET['examinerusername']))
{
    header("Location: examiner_dashboard.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = $_SESSION['examinercourse'];
$examinerusername=$_GET['examinerusername'];
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch admin info
$sql = "SELECT * FROM examiner WHERE username='$examinerusername'";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Info</title>
    <link rel="stylesheet" href="../admin/style/my_profile.css">
</head>
<body>
<button class="back-btn" onclick="window.history.back()">&larr;</button> <!-- Back Button -->
<div class="container">
    <img src="../images/examinerlogo.jpg" alt="Examiner Logo" class="admin-logo"> <!-- Admin Logo -->
    <h1>Teacher Information</h1>

    <?php
    if ($result->num_rows > 0) {
        // Output data of each row
        while($row = $result->fetch_assoc()) {
            echo "<div class='admin-info'>";
            echo "<div><strong>ID:</strong> " . $row["examiner_id"] . "</div>";
            echo "<div><strong>Username:</strong> " . $row["username"] . "</div>";
            echo "<div><strong>Total Semesters:</strong> " . $row["subject_name"] . "(".$row["subject_code"].")"."</div>";
            echo "</div>";

            echo "<div class='btn-container'>";
            echo "<button onclick=\"window.location.href='../change_password.php?id=".$row['examiner_id']."&type=examiner&course=bca'\">Change Password</button>";
            echo "</div>";
        }
    } else {
        echo "<p>No admin information found.</p>";
    }
    ?>

</div>

<?php
$conn->close();
?>

</body>
</html>
