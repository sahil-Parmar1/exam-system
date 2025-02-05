<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student login</title>
    <link rel="stylesheet" href="../admin/style/admin_login.css"></link>
</head>
<body>
    
    <div class="container">
      <h1>Student Login</h1>
      <?php
       if($_SERVER['REQUEST_METHOD']==='POST')
       {
        $course=$_POST['course'];
        $username=$_POST['username'];
        $password=$_POST['password'];
        $conn=new mysqli("localhost","root","",$course);
        if($conn->connect_error)
        {
            die("connection failed...".$conn->connect_error);
        }
        $sql = "SELECT password FROM student_info WHERE username=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();
        
         if($hashed_password==null || $hashed_password=="")
         {
            echo "<div class='error-message'>Invalid username or password..</div><br>";
         }
         else
         {
           if ($password==$hashed_password) {
            echo "<br> Login successful! <br>";
            session_start();
            $_SESSION['studentusername']=$username;
            $_SESSION['studentcourse']=$course;
            header("Location:student_dashboard.php");
            exit;

                    // Redirect to admin dashboard or perform other actions
                } else {
                    echo "<div class='error-message'>Invalid username or password.</div><br>";
                  
                  
                }  
         }
       
        $conn->close();
       }
      ?>
      <form action="" method="POST" id="form">
      <div class="form-group">
        
         <select name="course" id="course" required>
            <option value="">-- select course --</option>
            <option value="bca">bca</option>
            <option value="bba">bba</option>
            <option value="ba">ba</option>
            <option value="ma">ma</option>
            <option value="bcom">bcom</option>
            <option value="mcom">mcom</option>
         </select>
      </div>
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required/>
    </div>
    <div class="form-group">
       <label for="password">Password</label>
       <input type="password" id="password" name="password" required/>
    </div>
    <div class="form-group">
      <button type="submit" name="submit" id="submit" class="login-button">Login</button>
    </div>
    </form>
    <div class="form-group">
      <button onclick="window.location.href='../index.php'" class="bkbutton" >Back to Home</button>
    </div>

</div>

</body>
</html>