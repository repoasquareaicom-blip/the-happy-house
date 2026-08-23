<?php
include 'db.php';

// Set headers to force download CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Student_Wellbeing.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, ['Date', 'School Name', 'Year Level', 'Class Name', 'GLS', 'Worthwhile', 'Optimism', 'Control', 'Relationships', 'Achieving']);

if (isset($_GET['school_id'])) {
    $school_id = (int) $_GET['school_id'];

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
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['report_date'],
                $row['school_name'],
                $row['year_level_caption'],
                $row['class_group_caption'],
                $row['overall_wellbeing'],
                $row['worthwhile'],
                $row['optimism'],
                $row['control'],
                $row['relationships'],
                $row['achieving']
            ]);
        }
    } else {
        fputcsv($output, ['No records found for this class.']);
    }
}

// Close the output stream
fclose($output);
exit;
?>
