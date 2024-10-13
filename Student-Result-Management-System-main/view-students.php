<?php
session_start();
$showAlert = false;
$showError = false;
include "includes/connection.php"; // Keeping the connection as it was
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin']!=true){
    header("location: index.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" type="text/css" href="css/fp1.css?version=51">
     
    <!-- Datatable plugin CSS file -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css" />

    <!-- jQuery library file -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>

    <!-- Datatable plugin JS library -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

    <style>
        .backlog {
            font-weight: bold; /* Bold text for rows with backlogs */
        }
    </style>
</head>
<body>
    <?php include "nav.php";?>
    <div class="m2">
        <h1 style="text-align:center;">View Students</h1>
        <h3 style="margin: 20px; margin-bottom:50px">View Students Information</h3>
        <table id="tableID" class="display">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Roll No.</th>
                    <th>Branch</th>
                    <th>Semester</th>
                    <th>Reg Date</th>
                    <th>CGPA</th>
                    <th>Backlog</th>
                    <th>Transcript</th>    <!-- New column for Backlog count -->
                </tr>
            </thead> 
            <tbody>
<?php
$sql = "SELECT student.Name, student.Roll_No, student.Reg_date, student.reg_id, branch.branch, semester.semester 
        FROM student 
        JOIN branch ON student.branch_id = branch.branch_id 
        JOIN semester ON student.sem_id = semester.sem_id";
$result = mysqli_query($conn, $sql);
$c = 1;
$num = mysqli_num_rows($result);
if($num > 0){
    while($row = mysqli_fetch_assoc($result)){
        // Calculate the CGPA for each student
        $roll_no = $row['Roll_No'];
        $cgpa_query = "SELECT results.marks, subjects.credits 
                       FROM results 
                       JOIN subjects ON results.subj_id = subjects.subj_id 
                       WHERE results.roll_no = '$roll_no'";
        $cgpa_result = mysqli_query($conn, $cgpa_query);

        $total_credits = 0;
        $total_points = 0;
        $backlog_count = 0;

        while ($cgpa_row = mysqli_fetch_assoc($cgpa_result)) {
            $marks = $cgpa_row['marks'];
            $credits = $cgpa_row['credits'];

            // Calculate GPA based on marks
            $gpa = ($marks < 40) ? 0 : $marks / 10;

            if ($gpa == 0) {
                $backlog_count++; // Increment backlog count for failed subjects
            } else {
                $total_credits += $credits; // Sum up the credits for non-backlog subjects
                $total_points += ($gpa * $credits); // Calculate total grade points
            }
        }

        // Calculate CGPA: (Total Points Earned / Total Credits Earned)
        $cgpa = ($total_credits > 0) ? number_format($total_points / $total_credits, 2) : 0;

        // Apply bold text for rows with backlogs
        $row_class = ($backlog_count >= 1) ? 'backlog' : '';
        ?>
        <tr class="<?php echo $row_class; ?>"> <!-- Apply conditional class for backlogs -->
            <td><?php echo $c;?></td>
            <td><?php echo $row['Name'];?></td>
            <td><?php echo $row['Roll_No'];?></td>
            <td><?php echo $row['branch'];?></td>
            <td><?php echo $row['semester'];?></td>
            <td><?php echo $row['Reg_date'];?></td>
            <td><?php echo $cgpa;?></td> <!-- Updated CGPA column -->
            <td><?php echo $backlog_count; ?></td> <!-- Backlog column showing number of backlogs -->
            <td>
                <!-- Button to view the transcript -->
                <a href="view-transcript.php?roll_no=<?php echo $row['Roll_No'];?>" class="btn btn-primary">View Transcript</a>
            </td>
        </tr>
        <?php
        $c++;
    }
}
?>
            </tbody>

            <tfoot>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Roll No.</th>
                    <th>Branch</th>
                    <th>Semester</th>
                    <th>CGPA</th>
                    <th>
                        <!-- Custom range input fields in the table footer -->
                        <div class="range-filter-container">
                            <input type="number" id="min" placeholder="Min">
                            <input type="number" id="max" placeholder="Max">
                        </div>
                    </th>
                    <th>Backlog</th>
                </tr>
        </table>
        <script>
        $(document).ready(function() {
            // Extend the DataTables search to include custom range filtering
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var min = parseInt($('#min').val(), 10);
                    var max = parseInt($('#max').val(), 10);
                    var CGPA = parseFloat(data[6]) || 0; // Column index for 'Marks'

                    if ((isNaN(min) && isNaN(max)) ||
                        (isNaN(min) && CGPA <= max) ||
                        (min <= CGPA && isNaN(max)) ||
                        (min <= CGPA && CGPA <= max)) {
                        return true;
                    }
                    return false;
                }
            );

            var table = $('#tableID').DataTable({
                "dom": 'Bfrtip',
                "buttons": [
                    {
                        "extend": 'csv',
                        "text": 'Download CSV',
                        "exportOptions": {
                            "columns": ':visible'
                        }
                    }
                ],
                initComplete: function() {
                    this.api().columns().every(function() {
                        var column = this;

                        if (column.index() !== 6) {
                            var select = $('<select><option value=""></option></select>')
                                .appendTo($(column.footer()).empty())
                                .on('change', function() {
                                    var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                    column
                                        .search(val ? '^' + val + '$' : '', true, false)
                                        .draw();
                                });

                            column.data().unique().sort().each(function(d, j) {
                                select.append('<option value="' + d + '">' + d + '</option>');
                            });
                        }
                    });
                }
            });

            // Trigger table filtering and chart update when user enters a custom range
            $('#min, #max').on('keyup change', function() {
                table.draw();
                updateCharts(); // Update the charts after filtering
            });

           
              
        });
    </script>
    </div>
</body>
</html>
