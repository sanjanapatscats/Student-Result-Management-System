<?php
session_start();
$showAlert = false;
$showError = false;
include "includes/connection.php";
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
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
    <title>View Results</title>
    <link rel="stylesheet" type="text/css" href="css/fp1.css?version=51">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.4/css/buttons.dataTables.min.css" />
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1, h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
        }

        .low-marks {
            background-color: #f8d7da !important;
        }

        @media screen and (max-width: 600px) {
            table {
                font-size: 12px;
            }
        }

        tfoot input {
            width: 80px;
            padding: 3px;
            box-sizing: border-box;
            text-align: center;
        }

        tfoot .range-filter-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .chart-container {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <?php include "nav.php"; ?>
    <div class="container">
        <h1>View Results</h1>
        <h3>Student Result</h3>

        <table id="tableID" class="display">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Roll No.</th>
                    <th>Branch</th>
                    <th>Semester</th>
                    <th>Subject</th>
                    <th>Marks</th>
                    <th>GPA</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT student.Name, student.Roll_No, branch.branch, semester.semester, subjects.subj_name, results.marks 
                        FROM results 
                        JOIN student ON results.roll_no = student.Roll_No 
                        JOIN branch ON branch.branch_id = results.branch_id 
                        JOIN semester ON semester.sem_id = results.sem_id 
                        JOIN subjects ON subjects.subj_id = results.subj_id";
                $result = mysqli_query($conn, $sql);
                $c = 1;
                $num = mysqli_num_rows($result);
                if ($num > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $gpa = $row['marks'] < 40 ? 0 : floor($row['marks'] / 10) + 1;
                        $rowClass = $row['marks'] < 40 ? 'low-marks' : '';
                        ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td><?php echo $c; ?></td>
                            <td><?php echo $row['Name']; ?></td>
                            <td><?php echo $row['Roll_No']; ?></td>
                            <td><?php echo $row['branch']; ?></td>
                            <td><?php echo $row['semester']; ?></td>
                            <td><?php echo $row['subj_name']; ?></td>
                            <td><?php echo $row['marks']; ?></td>
                            <td><?php echo $gpa; ?></td>
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
                    <th>Student Name</th>
                    <th>Roll No.</th>
                    <th>Branch</th>
                    <th>Semester</th>
                    <th>Subject</th>
                    <th>
                        <!-- Custom range input fields in the table footer -->
                        <div class="range-filter-container">
                            <input type="number" id="minMark" placeholder="Min">
                            <input type="number" id="maxMark" placeholder="Max">
                        </div>
                    </th>
                    <th></th>
                </tr>
            </tfoot>
        </table>

        <div class="chart-container">
            <h3>Dynamic Student Performance Chart</h3>
            <canvas id="performanceChart"></canvas>
            <canvas id="avgMarksChart"></canvas>
            <canvas id="densityChart"></canvas> <!-- New density chart -->
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Extend the DataTables search to include custom range filtering
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var minMark = parseInt($('#minMark').val(), 10);
                    var maxMark = parseInt($('#maxMark').val(), 10);
                    var marks = parseFloat(data[6]) || 0; // Column index for 'Marks'

                    if ((isNaN(minMark) && isNaN(maxMark)) ||
                        (isNaN(minMark) && marks <= maxMark) ||
                        (minMark <= marks && isNaN(maxMark)) ||
                        (minMark <= marks && marks <= maxMark)) {
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
            $('#minMark, #maxMark').on('keyup change', function() {
                table.draw();
                updateCharts(); // Update the charts after filtering
            });

            // Function to update the charts based on filtered data
            function updateCharts() {
                var performanceData = {};
                var avgMarksData = {};
                var totalMarksData = {};
                var totalStudents = {};
                var densityData = {}; // For the density chart

                // Get filtered data from the table
                table.rows({ search: 'applied' }).every(function(rowIdx) {
                    var data = this.data();
                    var semester = data[4];
                    var marks = parseFloat(data[6]);
                    var subject = data[5];

                    // Count total marks and students per semester
                    if (!totalMarksData[semester]) {
                        totalMarksData[semester] = 0;
                        totalStudents[semester] = 0;
                    }
                    totalMarksData[semester] += marks;
                    totalStudents[semester]++;

                    // Count marks by subject
                    if (!performanceData[subject]) {
                        performanceData[subject] = [];
                    }
                    performanceData[subject].push(marks);

                    // Prepare data for the density chart (group marks by semester and subject)
                    if (!densityData[semester]) {
                        densityData[semester] = {};
                    }
                    if (!densityData[semester][subject]) {
                        densityData[semester][subject] = [];
                    }
                    densityData[semester][subject].push(marks);
                });

                // Prepare data for performance chart
                var performanceLabels = Object.keys(performanceData);
                var performanceChartData = performanceLabels.map(subject => {
                    return performanceData[subject].reduce((a, b) => a + b, 0) / performanceData[subject].length;
                });

                // Prepare data for average marks chart
                var avgMarksLabels = Object.keys(totalMarksData);
                var avgMarksData = avgMarksLabels.map(sem => {
                    return totalMarksData[sem] / totalStudents[sem];
                });

                // Create performance chart
                var ctx = document.getElementById('performanceChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: performanceLabels,
                        datasets: [{
                            label: 'Average Marks by Subject',
                            data: performanceChartData,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // Create average marks chart
                var ctxAvg = document.getElementById('avgMarksChart').getContext('2d');
                new Chart(ctxAvg, {
                    type: 'line',
                    data: {
                        labels: avgMarksLabels,
                        datasets: [{
                            label: 'Average Marks per Semester',
                            data: avgMarksData,
                            fill: false,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // Create density chart (line chart for performance over semesters and courses)
                var densityLabels = performanceLabels;
                var densityDatasets = [];

                Object.keys(densityData).forEach(semester => {
                    var semesterData = densityLabels.map(subject => {
                        if (densityData[semester][subject]) {
                            return densityData[semester][subject].reduce((a, b) => a + b, 0) / densityData[semester][subject].length;
                        } else {
                            return null;
                        }
                    });

                    densityDatasets.push({
                        label: `Semester ${semester}`,
                        data: semesterData,
                        fill: false,
                        borderColor: randomColor(), // Generate random color for each line
                        tension: 0.1
                    });
                });

                var ctxDensity = document.getElementById('densityChart').getContext('2d');
                new Chart(ctxDensity, {
                    type: 'line',
                    data: {
                        labels: densityLabels,
                        datasets: densityDatasets
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Helper function to generate random color
            function randomColor() {
                var r = Math.floor(Math.random() * 255);
                var g = Math.floor(Math.random() * 255);
                var b = Math.floor(Math.random() * 255);
                return `rgba(${r}, ${g}, ${b}, 1)`;
            }

            // Initial chart render
            updateCharts();
        });
    </script>
</body>
</html>
