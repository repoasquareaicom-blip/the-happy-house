<?php
$term = $_POST['term'];  // Search term from input
$date = $_POST['date'];  // Selected date filter

// Replace with actual database connection
$conn = new mysqli("localhost", "root", "", "school_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch students matching search term
$sql = "SELECT id, name FROM students WHERE name LIKE ? AND date = ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%$term%";
$stmt->bind_param("ss", $searchTerm, $date);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = ["id" => $row["id"], "value" => $row["name"]];
}

echo json_encode($students);
?>
