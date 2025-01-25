<?php
session_start();


if (isset($_GET['id'])) {
    $_SESSION['id'] = $_GET['id'];
}

if (!isset($_SESSION['examinerusername']) || !isset($_SESSION['examinercourse'])) {
    // Redirect to login page
    header("Location: examiner_login.php");
    exit;
}

if (!isset($_SESSION['id'])) {
    header("Location: examiner_dashboard.php");
    exit;
}

$id = $_SESSION['id'];
$exam_id = $_SESSION['exam_id'];
// Assuming you have a file for database connection
 // Assuming this file contains your database connection code

$conn = new mysqli("localhost", "root", "", $_SESSION['examinercourse']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$tablename = $exam_id . '_exam';
$query = "SELECT id, type, question, question_option, ans FROM $tablename WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $question_id = $row['id'];
    $question_type = $row['type'];
    $question_text = $row['question'];
    $question_option = $row['question_option'];
    $question_answer = $row['ans'];
} else {
    // Handle case where no question is found
    header("Location: examiner_dashboard.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $updated_type = $_POST['type'];
    $updated_question = $_POST['question'];
    $updated_question_option = $_POST['question_option'];
    $updated_answer = $_POST['ans'];

    $update_query = "UPDATE $tablename SET type = ?, question = ?, question_option = ?, ans = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssssi", $updated_type, $updated_question, $updated_question_option, $updated_answer, $id);

    if ($update_stmt->execute()) {
        // Redirect to a success page or display a success message
        header("Location: update_exam.php");
        exit;
    } else {
        // Handle update error
        echo "Error updating record: " . $conn->error;
    }

    $update_stmt->close();
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Question</title>
    <link rel="stylesheet" href="style/update_question.css">
</head>
<body>
    
    <form action="update_question.php" method="post">
    <h2>Update Question</h2>    
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($question_id); ?>">
        <label for="type">Type:</label>
        <input type="text" id="type" name="type" value="<?php echo htmlspecialchars($question_type); ?>" required><br><br>
        
        <label for="question">Question:</label>
        <textarea id="question" name="question" required><?php echo htmlspecialchars($question_text); ?></textarea><br><br>
        
        <label for="question_option">Options:</label>
        <textarea id="question_option" name="question_option" required><?php echo htmlspecialchars($question_option); ?></textarea><br><br>
        
        <label for="ans">Answer:</label>
        <input type="text" id="ans" name="ans" value="<?php echo htmlspecialchars($question_answer); ?>" required><br><br>
        
        <button type="submit" name="update">Update</button>
        <button type="button" onclick="window.location.href='update_exam.php'">Cancel</button>
    </form>
</body>
</html>