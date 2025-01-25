<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['course'])) {
    // Redirect to login page
    header("Location: admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete student</title>

</head>
<body>
<?php
if (isset($_GET['id'])) {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = $_SESSION['course'];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $id = intval($_GET['id']); // Sanitize the input

    // Delete record query
    $sql = "DELETE FROM student_info WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        // Drop the associated table
        $dropTableSql = "DROP TABLE IF EXISTS " . $id . "_student";
        $conn->query($dropTableSql);

        echo "<script>alert('Record deleted successfully'); window.location.href = 'student_list.php';</script>";
    } else {
        echo "<script>alert('Error deleting record: " . $conn->error . "'); window.location.href = 'student_list.php';</script>";
    }

    $conn->close();
}
?>
</body>
</html>
