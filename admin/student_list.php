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

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle AJAX request for fetching students
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['semester'])) {
    $selected_semester = $_POST['semester'];
    if ($selected_semester == 'all') {
        $sql = "SELECT * FROM student_info";
    } else {
        $sql = "SELECT * FROM student_info WHERE semester = '$selected_semester'";
    }
    $result = $conn->query($sql);

    $students = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }

    // Return JSON response
    echo json_encode($students);
    exit;
}

// Fetch semester data for the admin
$admin_username = $_SESSION['adminusername'];
$admin_sql = "SELECT semesters FROM admin_info WHERE username = '$admin_username'";
$admin_result = $conn->query($admin_sql);
if ($admin_result->num_rows > 0) {
    $admin_row = $admin_result->fetch_assoc();
    $_SESSION['semester'] = $admin_row['semesters'];
} else {
    die("No semester information found for the admin.");
}
// Call the JavaScript function to fetch students when the page loads

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
    <link rel="stylesheet" href="style/student_list.css">
    <script>
        
        // JavaScript function to confirm deletion
        function confirmDeletion(Id) {
            if (confirm("Are you sure you want to delete this exam? This action cannot be undone.")) {
                // Redirect to the same script with the exam_id to delete
                window.location.href = "delete_student.php?id=" + Id;
            } else {
                // Redirect back to the previous page
                
            }
        }
   
        function fetchStudents() {
            const semester = document.getElementById('semester').value;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'student_list.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const students = JSON.parse(xhr.responseText);
                    const studentList = document.getElementById('student-list');
                    let html = "<table border='1'><tr><th>Id</th><th>Roll</th><th>Name</th><th>Semester</th><th>Update</th><th>Change Password</th><th>Delete</th></tr>";
                    if (students.length > 0) {
                        students.forEach(student => {
                            html += `<tr>
                                <td>${student.id}</td>
                                <td>${student.roll}</td>
                                <td>${student.name}</td>
                                <td>${student.semester}</td>
                                <td><a href="updatestudent.php?id=${student.id}">Update</a></td>
                                <td><a href="../change_password.php?id=${student.id}&type=student&course=<?php echo $_SESSION['admincourse'];?>">Change Password</a></td>
                                 <td><button id='delete' onclick='confirmDeletion(${student.id})'>🗑️</button></td>
                            </tr>`;
                        });
                    } else {
                        html += "<tr><td colspan='6'>No students found for the selected semester.</td></tr>";
                    }
                    html += "</table>";
                    studentList.innerHTML = html;
                }
            };
            xhr.send(`semester=${semester}`);
        }
    </script>
</head>
<body>
  
    <a href="admin_dashboard.php" class="back-button">&larr;</a><br><br>
    <form>
        <label for="semester">Select Semester:</label>
        <select name="semester" id="semester" onchange="fetchStudents()">
            <option value="">--select the semester--</option>
            <option value="all">All</option>
            <?php
            for ($i = 1; $i <= $_SESSION['semester']; $i++) {
                echo "<option value='$i'>Semester $i</option>";
            }
            ?>
        </select>
    </form>
    <div id="student-list">
        <!-- Student data will be displayed here -->
    </div>
</body>
</html>
