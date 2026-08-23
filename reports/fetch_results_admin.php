<?php
include 'db.php';

if (isset($_POST['school_id'])) {
    $school_id = (int) $_POST['school_id'];

    $query = "
        SELECT 
            sm.school_name AS school_name,
            ym.year_level_caption,
            cg.class_group_caption,
            DATE_FORMAT(w.report_date, '%b %d, %Y') AS report_date, 
            w.start_time, 
            w.overall_wellbeing, 
            w.worthwhile, 
            w.optimism, 
            w.control, 
            w.relationships, 
            w.achieving
        FROM student_wellbeing_report w
        JOIN class_groups cg ON w.class_id = cg.id
        JOIN year_groups yg ON cg.year_id = yg.id
        JOIN master_year_level ym on yg.year_group_caption = ym.id
        JOIN school_master sm ON yg.school_id = sm.id
        WHERE sm.id = $school_id
        ORDER BY w.report_date ASC;
    ";



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
              echo "<a href='download_excel.php?school_id=$school_id' class='btn btn-elegant'>
              <i class='fas fa-file-csv'></i> Download CSV
            </a>";
      

        echo "<table class='table table-bordered table-hover custom-table'>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>School Name</th>
                        <th>Year Level</th>
                        <th>Class Name</th>
                        <th>GLS</th>
                        <th>Worthwhile</th>
                        <th>Optimism</th>
                        <th>Control</th>
                        <th>Relationships</th>
                        <th>Achieving</th>
                    </tr>
                </thead>
                <tbody>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['report_date']}</td>
                    <td>{$row['school_name']}</td>
                    <td>{$row['year_level_caption']}</td>
                    <td>{$row['class_group_caption']}</td>
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
