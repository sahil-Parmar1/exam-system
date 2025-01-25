<?php
session_start();

// Check if user is not logged in (session variables not set)
if (!isset($_SESSION['adminusername']) || !isset($_SESSION['admincourse'])) {
    // Redirect to login page
    header("Location: admin_login.php");
    exit;
}

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

// Fetch admin info
$sql = "SELECT * FROM admin_info";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Info</title>
    <link rel="stylesheet" href="style/my_profile.css">
</head>
<body>
<button class="back-btn" onclick="window.history.back()">Back</button> <!-- Back Button -->
<div class="container">
    <img src="../images/adminlogo.png" alt="Admin Logo" class="admin-logo"> <!-- Admin Logo -->
    <h1>Admin Information</h1>

    <?php
    if ($result->num_rows > 0) {
        // Output data of each row
        while($row = $result->fetch_assoc()) {
            echo "<div class='admin-info'>";
            echo "<div><strong>ID:</strong> " . $row["id"] . "</div>";
            echo "<div><strong>Username:</strong> " . $row["username"] . "</div>";
            echo "<div><strong>Total Semesters:</strong> " . $row["semesters"] . "</div>";
            echo "</div>";

            echo "<div class='btn-container'>";
            echo "<button onclick=\"window.location.href='../change_password.php?id=1&type=admin&course=bca'\">Change Password</button>";
            echo "<button onclick=\"window.location.href='change_semesters.php'\">Change Semesters</button>";
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
