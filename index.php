<?php
$courses = ["bca", "bba", "ba", "ma", "mcom", "bcom"];
$conn = new mysqli("localhost", "root", "");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

foreach ($courses as $course) {
    try {
        $conn->select_db($course);
    } catch (mysqli_sql_exception $e) {
        $conn->query("CREATE DATABASE IF NOT EXISTS $course");
        $conn->select_db($course);
        $result=$conn->query("CREATE TABLE admin_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,  -- Fixed: Added length for VARCHAR
    password VARCHAR(255) NOT NULL, -- Fixed: Added length for VARCHAR
    semesters INT NOT NULL,
    course VARCHAR(255) NOT NULL    -- Fixed: Added length for VARCHAR
)");
        if($result)
        {
            $username=$course."@"."admin";
            $password=$course."admin"."@"."1234";
            $hashpassword=password_hash($password,PASSWORD_DEFAULT);
        $conn->query("INSERT INTO admin_info (username, password, semesters, course) VALUES ('$username', '$hashpassword', 6, '$course')");
        echo "inserted sucessfully...";
         }

    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <link rel="stylesheet" href="index.css">
    <style>
        * {box-sizing:border-box}

            /* Slideshow container */
            .slideshow-container {
            max-width: 1000px;
            position: relative;
            margin: auto;
            }

            /* Hide the images by default */
            .mySlides {
            display: none;
            transition:opacity 1s ease-in-out;
            }

            /* Next & previous buttons */
            .prev, .next {
            cursor: pointer;
            position: absolute;
            top: 50%;
            width: auto;
            margin-top: -22px;
            padding: 16px;
            color: white;
            font-weight: bold;
            font-size: 18px;
            transition: 0.6s ease;
            border-radius: 0 3px 3px 0;
            user-select: none;
            }

            /* Position the "next button" to the right */
            .next {
            right: 0;
            border-radius: 3px 0 0 3px;
            }

            /* On hover, add a black background color with a little bit see-through */
            .prev:hover, .next:hover {
            background-color: rgba(0,0,0,0.8);
            }

            /* Caption text */
            .text {
            color: #D4F12DFF;
            font-size: 25px;
            padding: 8px 12px;
            position: absolute;
            bottom: 100px;
            width: 100%;
            text-align: center;
            }

            /* Number text (1/3 etc) */
            .numbertext {
            color: #f2f2f2;
            font-size: 12px;
            padding: 8px 12px;
            position: absolute;
            top: 0;
            }

            /* The dots/bullets/indicators */
            .dot {
            cursor: pointer;
            height: 15px;
            width: 15px;
            margin: 0 2px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
            transition: background-color 0.6s ease;
            }

            .active, .dot:hover {
            background-color: #717171;
            }

            /* Fading animation */
            .fade {
            animation-name: fade;
            animation-duration: 1.5s;
            }

            @keyframes fade {
            from {opacity: .4}
            to {opacity: 1}
            }
    </style>
</head>
<body>
    <div class="header">
        <div class="headertemplet">
        <img src="images/gdmodilogo.png" alt="logo">
        <div>
    <h1>GD Modi Collage</h1>
    <h3>Online Examination System</h3>
    </div>
    </div>
    <button class="freelinks" onclick="window.location.href='https://gdmca.ac.in/'">
    View Website &gt;
    </button>
    </div>
  
    
    
    <div class="main">
       
           <div class="card"> 
            <h2>Sign in As</h2>
             <div class="singinoption">
               <div class="option" onclick="window.location.href='admin/admin_login.php'">
                <img src="images/adminlogo.png" alt="admin" class="signinlogo">
                <h2>Admin</h2>
               </div>
               <div class="option" onclick="window.location.href='examiner/examiner_login.php'">
                <img src="images/examinerlogo.jpg" alt="examiner" class="signinlogo">
                <h2>Examiner</h2>
               </div>
               <div class="option" onclick="window.location.href='student/student_login.php'">
                <img src="images/studentlogo.webp" alt="student" class="signinlogo">
                <h2>Student</h2>
               </div>
             </div>
           </div><br>
          
           <!-- Slideshow container -->
<div class="slideshow-container">

<!-- Full-width images with number and caption text -->
<div class="mySlides fade">
  <div class="numbertext">1 / 4</div>
  <img src="images/slideshow/gate.jpg" style="width:100%">
  <div class="text">Your Future, One Test Away: Seamless, Secure, Smart Exams!</div>
</div>

<div class="mySlides fade">
  <div class="numbertext">2 / 4</div>
  <img src="images/slideshow/pexels-dothanhyb-5530437.jpg" style="width:100%">
  <div class="text">Empowering Education, One Click at a Time!</div>
</div>

<div class="mySlides fade">
  <div class="numbertext">3 / 4</div>
  <img src="images/slideshow/pexels-ron-lach-10638066.jpg" style="width:100%">
  <div class="text">Revolutionizing Assessments: Anywhere, Anytime Testing!</div>
</div>

<div class="mySlides fade">
  <div class="numbertext">4 / 4</div>
  <img src="images\slideshow\pexels-ron-lach-10638069.jpg" style="width:100%">
  <div class="text">Exams Simplified: Where Efficiency Meets Excellence!</div>
</div>
<!-- Next and previous buttons -->
<a class="prev" onclick="plusSlides(-1)">&#10094;</a>
<a class="next" onclick="plusSlides(1)">&#10095;</a>
</div>
<br>

<!-- The dots/circles -->
<div style="text-align:center">
<span class="dot" onclick="currentSlide(1)"></span>
<span class="dot" onclick="currentSlide(2)"></span>
<span class="dot" onclick="currentSlide(3)"></span>
<span class="dot" onclick="currentSlide(4)"></span>
</div>
<script>
let slideIndex = 0; // Initial slide index
let autoSlideInterval; // Variable to hold the automatic slideshow interval

// Initialize the slideshow
function initSlideshow() {
    showSlides(slideIndex); // Display the first slide
    startAutoSlide(); // Start automatic slideshow
}

// Display the current slide and update dots
function showSlides(n) {
    let slides = document.getElementsByClassName("mySlides");
    let dots = document.getElementsByClassName("dot");
    if (n >= slides.length) slideIndex = 0; // Wrap around to the first slide
    if (n < 0) slideIndex = slides.length - 1; // Wrap around to the last slide

    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none"; // Hide all slides
    }
    for (let i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", ""); // Remove active class from all dots
    }
    slides[slideIndex].style.display = "block"; // Show the current slide
    dots[slideIndex].className += " active"; // Add active class to the current dot
}

// Move to the next or previous slide manually
function plusSlides(n) {
    slideIndex += n;
    showSlides(slideIndex);
    resetAutoSlide(); // Restart the automatic slideshow
}

// Move to a specific slide manually
function currentSlide(n) {
    slideIndex = n;
    showSlides(slideIndex);
    resetAutoSlide(); // Restart the automatic slideshow
}

// Start the automatic slideshow
function startAutoSlide() {
    autoSlideInterval = setInterval(() => {
        slideIndex++;
        showSlides(slideIndex);
    }, 4000); // Change slide every 2 seconds
}

// Stop the automatic slideshow
function stopAutoSlide() {
    clearInterval(autoSlideInterval);
}

// Restart the automatic slideshow
function resetAutoSlide() {
    stopAutoSlide(); // Stop the current interval
    startAutoSlide(); // Start a new interval
}

// Initialize the slideshow on page load
window.onload = initSlideshow;
</script>
    </div>
    <div class="footer">
    <p>&copy; 2025 GD Modi. All rights reserved.</p>
    </div>
</body>
</html>