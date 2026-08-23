<?php
include 'db.php';

if (isset($_POST['activity_date']) && isset($_POST['class_id'])) {
    $activity_date = $conn->real_escape_string($_POST['activity_date']);
    $class_id = (int) $_POST['class_id'];

    // Query to fetch distinct students based on class and activity date
    $query = "
        SELECT DISTINCT s.id, s.name 
        FROM students s
        JOIN activity_by_class a ON s.id = a.student_id
        WHERE a.class_id = $class_id AND a.activity_date = '$activity_date'
        ORDER BY s.name ASC";

    $result = $conn->query($query);
    file_put_contents("log.txt", "student query $query", FILE_APPEND);
    if ($result->num_rows > 0) {
        echo "<tr><td><a href='#' class='all-students' data-class-id='$class_id''>Show Results for All Students</a></td></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td><a href='#' class='student-select' data-class-id='$class_id' data-student-id='{$row['id']}'>{$row['name']}</a></td></tr>";
        }
    } else {
        echo "<tr><td>No students found for the selected class and date.</td></tr>";
    }
}

?>
