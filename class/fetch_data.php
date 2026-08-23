<?php
// fetch_data.php
header('Content-Type: application/json');

$servername = "thehappyhouse.au";
$username = "thehappyhousedev";
$password = "T#2#@ppy#01$";
$dbname = "thehappyhouse";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['method'];

    if ($method === 'getActiveSubscription') {
        getActiveSubscription($conn);
    } else if ($method === 'getCancelledSubscription') {
        getCancelledSubscription($conn);
    } 
    else{
        echo json_encode(["error" => "Invalid method"]);
    }
} else {
    echo json_encode(["error" => "Invalid request"]);
}

function getActiveSubscription($conn) {
    $sql = "SELECT * from school_master WHERE status = 'active' and  now() BETWEEN subscription_start and subscription_end";
    
    $result = $conn->query($sql);

    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode($data);
}
function getCancelledSubscription($conn) {
    $sql = "SELECT * from school_master WHERE status = 'deleted'";
    $result = $conn->query($sql);

    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode($data);
}
$conn->close();
?>
