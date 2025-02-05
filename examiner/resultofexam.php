<?php
session_start();
if (!isset($_SESSION['examinerusername']) || !isset($_SESSION['examinercourse'])) {
    // Redirect to login page
    header("Location: examiner_login.php");
    exit;
}
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = $_SESSION['examinercourse'];
$examinerusername = $_SESSION['examinerusername'];
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch examinerusername from request (e.g., GET or POST)
$list=[];

if ($examinerusername) {
    // Prepare and bind
    $stmt = $conn->prepare("SELECT * FROM exams WHERE examinerusername = ?");
    $stmt->bind_param("s", $examinerusername);

    // Execute statement
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();

    // Check if any rows returned
    if ($result->num_rows > 0) {
        // Output data of each row
        while($row = $result->fetch_assoc()) {
            
            $examData= array(
                'exam_id' => $row["exam_id"],
                'exam_name' => $row["exam_name"],
                'semester' => $row["semester"]
            );
            array_push($list,$examData);
        }
    } else {
        echo "0 results";
    }

    // Close statement
    $stmt->close();
} else {
    echo "No examiner username provided.";
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam List</title>
</head>
<body>
    <h1>Select Exam</h1>
    <form action="process_exam_selection.php" method="post">
        <label for="exam">Choose an exam:</label>
        <select name="exam" id="exam">
            <option value="">--select exmas---</option>
            <?php foreach ($list as $exam): ?>
                <option value="<?php echo htmlspecialchars($exam['exam_name']); ?>">
                    <?php echo htmlspecialchars($exam['exam_name']) . " (semester:" . htmlspecialchars($exam['semester']).")"; ?>
                </option>
            <?php endforeach; ?>
        </select>
       <div class="result">

       </div>
    </form>
</body>
</html>