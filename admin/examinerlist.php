<?php
session_start();

// Check if user is not logged in (session variables not set)
if (!isset($_SESSION['adminusername']) || !isset($_SESSION['admincourse'])) {
    // Redirect to login page
    header("Location: admin_login.php");
    exit;
}
$servername = "localhost";
$username = "root";
$password = "";
$dbname = $_SESSION['admincourse'];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM examiner";
$result = $conn->query($sql);
$listofexminer = [];
if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        $listofexminer[] = $row;
    }
} else {
    echo "0 results";
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examiner List</title>
    <link rel="stylesheet" href="style/examinerlist.css">
    <script>
         function confirmDeletion(Id) {
            if (confirm("Are you sure you want to delete this exam? This action cannot be undone.")) {
                // Redirect to the same script with the exam_id to delete
                window.location.href = "delete_examiner.php?id=" + Id;
            } else {
                // Redirect back to the previous page
                
            }
        }
    </script>
</head>
<body> 
    <a href="admin_dashboard.php" class="back-button">&larr;</a>
    <h1>Teachers</h1>
   
  
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Username</th>
            <th>Subject code</th>
            <th>Subject</th>
            <th>Update</th>
            <th>Delete</th>
            <th>change Password</th>
        </tr>
        <?php foreach ($listofexminer as $examiner): ?>
        <tr>
            <td><?php echo htmlspecialchars($examiner['examiner_id']); ?></td>
            <td><?php echo htmlspecialchars($examiner['name']); ?></td>
            <td><?php echo htmlspecialchars($examiner['username']); ?></td>
            <td><?php echo htmlspecialchars($examiner['subject_code']); ?></td>
            <td><?php echo htmlspecialchars($examiner['subject_name']); ?></td>
            <td><a href="examiner_update.php?id=<?php echo $examiner['examiner_id']; ?>">Update</a></td>
            <td><button id='delete' onclick='confirmDeletion(<?php echo $examiner['examiner_id']; ?>)'>🗑️</button></td>
            <td><a href="../change_password.php?id=<?php echo $examiner['examiner_id']; ?>&type=examiner&course=<?php echo $_SESSION['admincourse'];?>">Change Password</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>