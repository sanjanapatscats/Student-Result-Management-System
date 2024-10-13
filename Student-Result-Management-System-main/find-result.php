<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Result</title>
    <link rel="stylesheet" type="text/css" href="css/fp1.css?version=51">
    <style>
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center; /* Center everything in the container */
        }

        .container h2 {
            margin-bottom: 20px;
        }

        input[type="text"] {
            width: 100%; /* Keep the textbox width as is */
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #28a745; /* Set the same shade of green */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #218838; /* Darker shade of green on hover */
        }
    </style>
</head>
<body>
    <?php include "nav.php"; ?>
    <div class="container">
        <h2>Find Your Result</h2>
        <form action="student-result.php" method="get"> <!-- Updated action to redirect to student-result.php -->
            <label for="roll_no">Enter Roll Number:</label>
            <input type="text" id="roll_no" name="roll_no" required>
            <input type="submit" value="View Transcript">
        </form>
    </div>
</body>
</html>
