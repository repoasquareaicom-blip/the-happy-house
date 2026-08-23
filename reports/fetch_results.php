<?php

include 'db.php';



if (isset($_POST['class_id'])) {

    $class_id = (int) $_POST['class_id'];



    $query = "

                SELECT

            w.student_name AS student_name, 

            DATE_FORMAT(w.report_date, '%b %d, %Y') AS report_date, 

            w.start_time, 

            w.overall_wellbeing, 

            w.worthwhile, 

            w.optimism, 

            w.control, 

            w.relationships, 

            w.achieving

        FROM student_wellbeing_report w

        WHERE w.class_id = $class_id

        ORDER BY w.report_date ASC;";



    



    $result = $conn->query($query);



    if ($result->num_rows > 0) {

        echo "<style>

                body, html {

                    margin: 0;

                    padding: 0;

                    width: 100%;

                    overflow-x: hidden;

                }

                .custom-table {

                    width: 100%;

                    border-collapse: collapse;

                }

                .custom-table th {

                    background-color: #4C7AA2 !important;

                    color: white;

                    padding: 12px;

                    text-align: center;

                    border: 1px solid #ddd;

                }

                .custom-table td {

                    background-color: #F8F1F0;

                    color: #333;

                    padding: 10px;

                    text-align: center;

                    border: 1px solid #ddd;

                }

                .score-highlight {

                    font-weight: bold;

                    color: #4C7AA2;

                }

              </style>";



        echo "<table class='table table-bordered table-hover custom-table'>

                <thead>

                    <tr>

                        <th>Student Name</th>

                        <th>Date</th>

                        <th>Start Time</th>

                        <th>Overall Well-being</th>

                        <th>Worthwhile</th>

                        <th>Optimism</th>

                        <th>Control</th>

                        <th>Relationships</th>

                        <th>Achieving</th>

                    </tr>

                </thead>

                <tbody>";



        while ($row = $result->fetch_assoc()) {
            $startTime = !empty($row['start_time'])
            ? date('h:i A', strtotime($row['start_time']))
            : '-';
            echo "<tr>

                    <td>{$row['student_name']}</td>

                    <td>{$row['report_date']}</td>

                   <td>{$startTime}</td>
            

                    <td class='score-highlight'>{$row['overall_wellbeing']}</td>

                    <td class='score-highlight'>{$row['worthwhile']}</td>

                    <td class='score-highlight'>{$row['optimism']}</td>

                    <td class='score-highlight'>{$row['control']}</td>

                    <td class='score-highlight'>{$row['relationships']}</td>

                    <td class='score-highlight'>{$row['achieving']}</td>

                  </tr>";

        }



        echo "</tbody></table>";

    } else {

        echo "<p>No records found for this class.</p>";

    }

}

?>

