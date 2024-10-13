<?php
$login = false;
$showError = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'includes/connection.php';
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM admin WHERE username='$username'";
    $result = mysqli_query($conn, $sql);
    $num = mysqli_num_rows($result);
    if ($num == 1) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password'])) {
                $login = true;
                session_start();
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $username;
                header("location: dashboard.php");
            } else {
                $showError = "Invalid Credentials";
            }
        }
    } else {
        $showError = "Invalid Credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRMS</title>
    <link rel="shortcut icon" href="sample/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="css/index.css">
    <style>
        body {
            background-color: lightgreen; /* Set light green background */
            background-image: none; /* Ensure no background image */
        }

        #f1, #f2 {
            display: flex; /* Use flexbox for side-by-side layout */
            justify-content: space-between; /* Space between the two boxes */
            align-items: flex-start; /* Align items to the top */
        }

        .external-link-btn {
            display: inline-block;
            padding: 12px 20px;
            font-size: 18px;
            background-color: #4CAF50; /* Green button color */
            color: white;
            text-decoration: none; /* Remove underline */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 30px; /* Spacing below the button */
        }

        .external-link-btn:hover {
            background-color: #45a049; /* Darker green on hover */
        }

        .centered-button {
            display: flex;
            justify-content: center;
            margin: 20px 0; /* Top and bottom margins for spacing */
        }
    </style>
</head>

<body>
    <nav>
        R.V College of Engineering - Placement and Result Management
    </nav>

    <!-- Centered Button above the div elements -->
    <div class="centered-button">
        <a href="https://placementportalrvce.netlify.app/" class="external-link-btn">Visit Placement Portal</a>
    </div>

    <div id="f2">
        <div id="f22" class="1">
            <div style="font-size: 30px; border-bottom: solid gray 3px; padding: 25px;">
                For Students
            </div>
            <div style="padding-top: 80px;">
                <label for="click" style="font-size: 25px;">Search your result :</label>
                <a href="find-result.php" style="border: solid ForestGreen 2px; padding: 10px; font-size: 20px; background-color: ForestGreen; color: white; text-decoration: none;">Click here</a>
            </div>
        </div>
    </div>

    <div id="f1">
        <div id="f11" class="1">
            <form action="" method="post">
                <div style="font-size: 30px; border-bottom: solid gray 3px; padding: 25px;">
                    Admin Login
                </div>
                <div style="padding-top: 45px; display: flex; align-items: center;">
                    <label for="username" style="font-size: 20px; width: 120px;">Username</label>
                    <input type="text" id="username" name="username" required style="padding: 7px; width: 400px; font-size: 17px;">
                </div>
                <br>
                <div style="padding-top: 7px; display: flex; align-items: center;">
                    <label for="password" style="font-size: 20px; width: 120px;">Password</label>
                    <input type="password" id="password" name="password" required style="padding: 7px; width: 400px; font-size: 17px;">
                </div>
                <br>
                <div style="padding-top: 15px;">
                    <button type="submit" style="border: solid ForestGreen 2px; padding: 7px; font-size: 20px; background-color: ForestGreen; color: white; width: 100px;">Login</button>
                </div>
                <?php
                if ($showError) {
                    echo '<div style="color: red; margin-top: 15px;">' . $showError . '</div>';
                }
                ?>
            </form>
        </div>
    </div>
    <div class="clearfix"></div>
</body>

</html>
