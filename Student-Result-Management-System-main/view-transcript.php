<?php
session_start();
include "includes/connection.php";

// Check if the student roll number is provided
if (!isset($_GET['roll_no'])) {
    echo "No student selected.";
    exit;
}

// Retrieve the student's roll number from the URL parameter
$roll_no = $_GET['roll_no'];

// Fetch student's information
$student_query = "SELECT student.Name, student.Roll_No, branch.branch, semester.semester 
                  FROM student 
                  JOIN branch ON student.branch_id = branch.branch_id 
                  JOIN semester ON student.sem_id = semester.sem_id 
                  WHERE student.Roll_No = '$roll_no'";
$student_result = mysqli_query($conn, $student_query);
$student_info = mysqli_fetch_assoc($student_result);

if (!$student_info) {
    echo "Invalid student.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Transcript - <?php echo $student_info['Name']; ?></title>
    <link rel="stylesheet" type="text/css" href="css/fp1.css?version=51">
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
        }

        .container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 8px 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .transcript-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <?php include "nav.php"; ?>
    <div class="container">
        <h2>Academic Transcript</h2>
        <h3>Student Name: <?php echo $student_info['Name']; ?></h3>
        <h3>Roll No.: <?php echo $student_info['Roll_No']; ?></h3>
        <h3>Branch: <?php echo $student_info['branch']; ?></h3>

        <?php
        // Fetch transcript data semester-wise for the student with subject codes
        $transcript_query = "SELECT semester.semester, subjects.subj_name, subjects.subj_code, subjects.credits, results.marks 
                             FROM results 
                             JOIN semester ON results.sem_id = semester.sem_id 
                             JOIN subjects ON results.subj_id = subjects.subj_id 
                             WHERE results.roll_no = '$roll_no' 
                             ORDER BY semester.semester";
        $transcript_result = mysqli_query($conn, $transcript_query);

        // Store results semester-wise
        $semesters = [];
        $backlog_subjects = []; // To store backlog subjects
        while ($row = mysqli_fetch_assoc($transcript_result)) {
            $semesters[$row['semester']][] = $row;

            // Check if marks are less than 40 to consider as backlog
            if ($row['marks'] < 40) {
                $backlog_subjects[] = $row;
            }
        }

        $total_credits_all_semesters = 0;
        $total_sgpa_all_semesters = 0;
        $num_semesters = count($semesters);

        // Function to calculate GPA based on marks
        function calculate_gpa($marks) {
            if ($marks < 40) return 0;
            if ($marks >= 40 && $marks < 50) return 5;
            if ($marks >= 50 && $marks < 60) return 6;
            if ($marks >= 60 && $marks < 70) return 7;
            if ($marks >= 70 && $marks < 80) return 8;
            if ($marks >= 80 && $marks < 90) return 9;
            return 10;  // For marks >= 90
        }

        // Display results semester-wise
        foreach ($semesters as $semester => $subjects) {
            echo "<h4>Semester: $semester</h4>";
            echo "<table class='transcript-table'>";
            echo "<tr><th>Subject Code</th><th>Subject</th><th>Credits</th><th>Marks</th><th>GPA</th></tr>";

            $total_credits = 0;
            $total_points = 0;

            foreach ($subjects as $subject) {
                $marks = $subject['marks'];
                $credits = $subject['credits'];
                $gpa = calculate_gpa($marks); // GPA calculation based on marks

                $total_credits += $credits;
                $total_points += ($gpa * $credits);

                echo "<tr>
                        <td>{$subject['subj_code']}</td>
                        <td>{$subject['subj_name']}</td>
                        <td>{$credits}</td>
                        <td>{$marks}</td>
                        <td>{$gpa}</td>
                      </tr>";
            }

            // Calculate SGPA for the semester
            $sgpa = $total_points / $total_credits;
            echo "<tr><td colspan='4'><strong>SGPA</strong></td><td><strong>" . number_format($sgpa, 2) . "</strong></td></tr>";
            echo "</table>";

            // Accumulate totals for CGPA calculation
            $total_credits_all_semesters += $total_credits;
            $total_sgpa_all_semesters += $sgpa;
        }

        // Calculate CGPA for all semesters
        $cgpa = $total_sgpa_all_semesters / $num_semesters;
        echo "<h3>Overall CGPA: " . number_format($cgpa, 2) . "</h3>";

        // If there are backlogs, display them in a separate table
        if (!empty($backlog_subjects)) {
            echo "<h3>Backlog Subjects</h3>";
            echo "<table class='transcript-table'>";
            echo "<tr><th>Semester</th><th>Subject Code</th><th>Subject</th><th>Credits</th><th>Marks</th></tr>";

            foreach ($backlog_subjects as $backlog) {
                echo "<tr>
                        <td>{$backlog['semester']}</td>
                        <td>{$backlog['subj_code']}</td>
                        <td>{$backlog['subj_name']}</td>
                        <td>{$backlog['credits']}</td>
                        <td>{$backlog['marks']}</td>
                      </tr>";
            }
            echo "</table>";
        }
        ?>
    </div>
</body>
</html>
