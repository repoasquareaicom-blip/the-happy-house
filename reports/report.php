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
    <style>
        .container {
            max-width: 100%;
            text-align: center;
            margin:0px;
        }

        /* Hide class group dropdown and results initially */
        #classGroupContainer, #wellbeing-result {
            display: none;
        }

        /* Back link styling */
        .back-link {
            display: inline-block;
            margin: 10px 0;
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }

        /* Dropdown container */
        .dropdown-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            margin-top: 50px;
        }

        /* Style for dropdowns */
        .form-select {
            width: 400px !important;
            text-align: center;
            margin: auto;
        }

        /* Custom style for dropdown */
        .group-ddl {
            background-color: #4C7AA2 !important;
            color: white !important;
            border: 1px solid blue;
        }

        /* Table container */
        #wellbeing-result {
            width: 100%;
            text-align: left;
            padding: 20px;
        }

        /* Position the back link on top left of table */
        #backToClass {
            font-size: 14px;
            font-weight: bold;
            position: absolute;
            left: 20px;
            top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Wellbeing & Game Score Results</h2>

        <!-- Year Level Dropdown -->
        <div id="yearLevelContainer" class="dropdown-container">
            <label for="yearLevel" class="form-label">Select Year Level:</label>
            <select id="yearLevel" class="form-select text-primary group-ddl">
                <option value="">-- Select Year Level --</option>
                <?php
                $school_id = $_SESSION['school_id'];
                $result = $conn->query("SELECT yg.id as id, yl.year_level_caption as year_group_caption 
                                        FROM year_groups yg 
                                        JOIN master_year_level yl ON yg.year_group_caption = yl.id 
                                        WHERE school_id= '$school_id' AND is_deleted=0 
                                        ORDER BY year_group_caption ASC;");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['year_group_caption']}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Class Group Dropdown -->
        <div id="classGroupContainer" class="dropdown-container">
            <label id="classLabel" class="form-label"></label>
            <select id="classGroup" class="form-select text-primary group-ddl">
                <option value="">-- Select Class Group --</option>
            </select>
            <span class="back-link" id="backToYear">Back</span>
        </div>

        <!-- Wellbeing Results Table -->
        <div id="wellbeing-result">
            <span class="back-link" id="backToClass">⬅ Back</span>
            <div id="result-content"></div>
        </div>
    </div>

    <script>
    $(document).ready(function () {
        let $yearLevel = $("#yearLevel");
        let $classGroup = $("#classGroup");

        // Function to handle year selection
        function handleYearSelection() {
            let yearId = $yearLevel.val();
            let yearText = $("#yearLevel option:selected").text();

            if (yearId) {
                $("#yearLevelContainer").hide();
                $("#classLabel").html("Classes in Year Level Group <span style='color:#4C7AA2;font-weight:bold'>" + yearText + "</span>");
                $("#classGroupContainer").show();

                // Fetch class groups using AJAX
                $.post("fetch_classes.php", { year_id: yearId })
                    .done(function (data) {
                        $classGroup.empty();
                        try {
                            let classes = JSON.parse(data);
                            if (classes.length === 0) {
                                $classGroup.append("<option value=''>No Class Group Available</option>");
                            } else {
                                $classGroup.append("<option value=''>-- Select Class Group --</option>");
                                $.each(classes, function (index, item) {
                                    $classGroup.append("<option value='" + item.id + "'>" + item.class_group_caption + "</option>");
                                });
                            }
                        } catch (e) {
                            console.error("Invalid JSON response", e);
                            $classGroup.append("<option value=''>Error loading classes</option>");
                        }
                    })
                    .fail(function (xhr, status, error) {
                        console.error("Error fetching class groups:", error);
                        $classGroup.html("<option value=''>Error loading classes</option>");
                    });
            }
        }

        // Handle year level change
        $yearLevel.change(handleYearSelection);

        // Handle class selection
        $classGroup.change(function () {
            let classId = $(this).val();
            let yearId = $yearLevel.val();

            if (classId) {
                fetchResults(classId, yearId);
            }
        });

        // Back button to return to year level selection
        $("#backToYear").click(function () {
            $("#classGroupContainer").hide();
            $("#yearLevelContainer").show();
            $yearLevel.val("").change(); // Reset selection
        });

        // Back button to return to class selection
        $("#backToClass").click(function () {
            $("#wellbeing-result").hide();
            $("#classGroupContainer").show();
            $("#classGroup").val(""); // Reset class dropdown selection
        });

        // Function to fetch results
        function fetchResults(classId, yearId) {
            if (!yearId || !classId) {
                $("#result-content").html("<p>No wellbeing & game results found</p>");
                $("#wellbeing-result").show();
                $("#classGroupContainer").hide();
                return;
            }

            $.post("fetch_results.php", { class_id: classId }, function (data) {
                let trimmedData = $.trim(data);
               
                $("#result-content").html(trimmedData);
                $("#wellbeing-result").show();
                $("#classGroupContainer").hide();

            });
        }
    });
    </script>
</body>
</html>
