<?php
include 'db.php';
$year_id = $_POST['year_id'];
file_put_contents('logs.txt', "in $year_id ", FILE_APPEND);
if ($year_id == "") $year_id = -1;
file_put_contents('logs.txt', "in $year_id ", FILE_APPEND);
$query="SELECT id, class_group_caption FROM class_groups WHERE year_id = $year_id";
file_put_contents('logs.txt', "in $query ", FILE_APPEND);
$result = $conn->query($query);
//  while ($row = $result->fetch_assoc()) {
//      echo "<tr><td><a href='#' class='class-select' data-class-id='{$row['id']}'>{$row['class_group_caption']}</a></td></tr>";
//  }

while ($row = $result->fetch_assoc()) {
    $classes[] = $row;
}

echo json_encode($classes);

?>
