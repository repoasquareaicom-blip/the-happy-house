<?php
session_start();
$servername = "thehappyhouse.au";
$username = "thehappyhousedev";
$password = "T#2#@ppy#01$";
$dbname = "thehappyhouse";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>School Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .container {
            max-width: 100%;
            text-align: center;
            margin: 0px;
        }
        .dropdown-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            margin-top: 50px;
        }
        .form-select {
            width: 400px !important;
            text-align: center;
            margin: auto;
        }
        .group-ddl {
            background-color: #4C7AA2 !important;
            color: white !important;
            border: 1px solid blue;
        }
        #wellbeing-result {
            width: 100%;
            text-align: left;
            padding: 20px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Wellbeing & Game Score Results</h2>
        <div class="dropdown-container">
            <label for="school" class="form-label">Select School:</label>
            <select id="school" class="form-select text-primary group-ddl">
                <option value="">-- Select School --</option>
                <?php
                $result = $conn->query("SELECT id, school_name FROM school_master WHERE subscription_status_id = 1;");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['school_name']}</option>";
                }
                ?>
            </select>
        </div>
        <div id="wellbeing-result">
            <div id="result-content"></div>
        </div>
    </div>
    <script>
    $(document).ready(function () {
        $("#school").change(function () {
            let schoolId = $(this).val();
            if (schoolId) {
                $.post("fetch_results_admin.php", { school_id: schoolId }, function (data) {
                    $("#result-content").html(data);
                    $("#wellbeing-result").show();
                });
            } else {
                $("#wellbeing-result").hide();
            }
        });
    });
    </script>
</body>
</html>
