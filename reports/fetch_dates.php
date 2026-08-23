<?php
include 'db.php';

$class_id = $_POST['class_id'];
$query = "SELECT DATE_FORMAT(activity_date, '%b %e, %Y') AS activity_date, activity_date AS actualDateFormat  
          FROM activity_by_class  
          WHERE class_id = $class_id 
          ORDER BY actualDateFormat ASC";
$result = $conn->query($query);
echo $query;
while ($row = $result->fetch_assoc()) {
    echo "<tr><td><a href='#' class='date-select' style='text-decoration: none;' data-class_id='$class_id' data-activity_date='{$row['actualDateFormat']}'>{$row['activity_date']}</a></td></tr>";
}
?>
