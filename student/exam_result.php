<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Page</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #ffffff;
        }
        .result-container {
            background-color: #2c3e50;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            padding: 30px 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            position: relative;
        }
        .result-container h1 {
            font-size: 28px;
            color: #ecf0f1;
            margin-bottom: 25px;
        }
        .result-item {
            font-size: 20px;
            margin: 15px 0;
            color: #bdc3c7;
        }
        .result-item span {
            font-weight: bold;
            color: #EAF1F0FF;
        }
        .obtain
        {
            font-weight: bold;
            color: #0BF7D3FF;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #95a5a6;
        }
        .back-button {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: #1abc9c;
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #16a085;
        }
        .highlight {
            font-size: 18px;
            margin-top: 20px;
            color: #f39c12;
        }
    </style>
</head>
<body>
    <?php
        // Extract query string parameters
        $total_questions = isset($_GET['total_question']) ? htmlspecialchars($_GET['total_question']) : 'N/A';
        $total_marks = isset($_GET['total_marks']) ? htmlspecialchars($_GET['total_marks']) : 'N/A';
        $negative_marks = isset($_GET['negative_marks']) ? htmlspecialchars($_GET['negative_marks']) : 'N/A';
        $obtain_marks = isset($_GET['obtain_marks']) ? htmlspecialchars($_GET['obtain_marks']) : 'N/A';

        // Calculate percentage and display a remark
       
       
    ?>
 <a href="student_dashboard.php" class="back-button"><- Back to Home</a>
    <div class="result-container">
       
        <h1>Examination Result</h1>
        <div class="result-item">Total Questions: <span><?php echo $total_questions; ?></span></div>
        <div class="result-item">Total Marks: <span><?php echo $total_marks; ?></span></div>
        <div class="result-item">Negative Marks: <span><?php echo $negative_marks; ?></span></div>
        <div class="obtain">Obtained Marks: <span ><?php echo $obtain_marks; ?></span></div>
       
        
    </div>
</body>
</html>
