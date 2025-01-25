<?php
session_start();
if (!isset($_SESSION['adminusername']) || !isset($_SESSION['admincourse'])) {
    // Redirect to login page
    header("Location: admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete examiner</title>

</head>
<body>
<?php
if (isset($_GET['id'])) {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = $_SESSION['admincourse'];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $id = intval($_GET['id']); // Sanitize the input

    // Delete record query
    $sql = "DELETE FROM examiner WHERE examiner_id = $id";
    if ($conn->query($sql) === TRUE) {
        // Drop the associated table
        

        echo "<script>alert('Record deleted successfully'); window.location.href = 'examinerlist.php';</script>";
    } else {
        echo "<script>alert('Error deleting record: " . $conn->error . "'); window.location.href = 'examinerlist.php';</script>";
    }

    $conn->close();
}
?>
</body>
</html>
