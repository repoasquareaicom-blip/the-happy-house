<?php
session_start();
include 'config/data.php';
include 'class/log.php';
include 'class/class_manage_groups.php';

$login_url = "school_admin_login.php";
// $_SESSION['school_admin_login_status'] = "true";
// $_SESSION['school_id'] = "1";

if (!isset($_SESSION['school_admin_login_status']) || $_SESSION['school_admin_login_status'] !== "true") {
    echo '<script type="text/javascript">';
    echo 'window.top.location.href = "' . $login_url . '";';
    echo '</script>';
    exit; // Stop script execution immediately
}

$_data = new Data();
$grouop = new Groups($_data);

$_log = new mLog();
$_log->writelog("year level : in");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    if ($action === 'create') {
        $year_group_caption = $_POST['txt_year_group_caption'];
        $_log->writelog("year level value : $year_group_caption");
        $result = $grouop->add_year_group($_SESSION['school_id'], $year_group_caption);
        echo $result;
        exit;
    } elseif ($action === 'edit') {
        $id = $_POST['selected_year_group_id'];
        $year_group_caption = $_POST['txt_year_group_caption'];
        $result = $grouop->edit_year_group($id, $year_group_caption);
        echo $result;
        exit;
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        $result = $grouop->delete_year_group($id);
        echo $result;
        exit;
    }
}

$years = $grouop->fetch_all_year_groups($_SESSION['school_id']);
$year_level_master_data = $grouop->fetch_year_level_master_data();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Year Levels</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/pages/dashboard.css">
    <link rel="stylesheet" href="assets/css/global.css">
    
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- Bootstrap Tooltip Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
       .modal {
    display: none;
    position: fixed;
    z-index: 2000; /* Ensure it’s above other content */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5); /* Slightly dark background */
}

.modal-content {
    background-color: #f9f9f9;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 10px;
    width: 50%;
    position: relative; /* For proper alignment */
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f0f0f0; /* Light background for header */
    border-bottom: 1px solid #ccc;
}

.close {
    color: #333;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
    padding: 0 10px;
}

.close:hover {
    color: #000;
    text-decoration: none;
}

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        
    </style>
</head>
<body>
    <h1>Manage Year Levels</h1>
    <button class="logo-theme-button margin-bt-10 " onclick="openModal('create')">Add New Year Level</button>
    <table id="groupDataTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="display:none">ID</th>
                <th>Year Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($years as $year): ?>
                <tr>
                    <td style="display:none"><?= htmlspecialchars($year['id']) ?></td>
                    <td><?= htmlspecialchars($year['year_group_caption']) ?></td>
                    <td>
                    <button onclick="openModal('edit', <?= htmlspecialchars(json_encode($year)) ?>)" class="btn btn-primary text-white">
                        Edit
                    </button>
                    <button onclick="deleteYear(<?= htmlspecialchars($year['id']) ?>)" class="btn btn-danger">
                        Delete
                    </button>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div id="year-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Create/Edit Year</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form id="year-form">
            <input type="hidden" name="action" id="form-action">
            <input type="hidden" name="selected_year_group_id" id="year-id">
            <!-- <label for="txt_year_group_caption">Select Year:</label>
            <input type="text" name="txt_year_group_caption" id="year-name" placeholder="Year Name" required> -->
            <!-- <label for="txt_year_group_caption">Select Year:</label> -->
            <select name="txt_year_group_caption" id="year-name" class="form-select form-select-lg mb-3 border border-primary rounded-3 p-2" required>
                <option value="">-- Select Year --</option>
                <?php foreach ($year_level_master_data as $year): ?>
                    <option value="<?= htmlspecialchars($year['id']) ?>">
                        <?= htmlspecialchars($year['year_level_caption']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
             <!-- Error message label -->
            <label id="error-message" class="text-danger d-block">&nbsp;</label>
            <br>
            <button type="button" onclick="saveYear()" class="logo-theme-button right-65">Save</button>
        </form>
    </div>
</div>


    <script>
        const modal = document.getElementById('year-modal');

        function openModal(action, year = null) {
            const errorLabel = document.getElementById('error-message');
            errorLabel.textContent = "";
            document.getElementById('form-action').value = action;
            document.getElementById('modal-title').innerText = action === 'edit' ? 'Edit Year Level' : 'Create Year Level';
            document.getElementById('year-id').value = year ? year.id : '';
            //document.getElementById('year-name').value =  year ? year.id : '';

            let yearDropdown = document.getElementById('year-name');
            let yearText = year ? year.year_group_caption : '';

            for (let option of yearDropdown.options) {
                if (option.textContent.trim() === yearText) {
                    option.selected = true;
                    break;
                }
            }
            modal.style.display = 'block';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        window.onclick = function (event) {
            if (event.target === modal) {
                closeModal();
            }
        }

        function saveYear() {
            const form = document.getElementById('year-form');
            const yearNameInput = document.getElementById('year-name');
            const yearName = yearNameInput.value.trim();
            const errorLabel = document.getElementById('error-message');
            const action = document.getElementById('form-action').value;
            const selectedYearId = document.getElementById('year-id').value; // Hidden field for edit
            
            let yearDropdown = document.getElementById('year-name');
            let yearText = yearDropdown.options[yearDropdown.selectedIndex].text;
            
            // Clear error when the user starts editing/selecting again
            yearNameInput.addEventListener('input', function () {
                errorLabel.textContent = "";
            });
            // Clear previous error message
            errorLabel.textContent = "";

            // Validate if year name is entered
            if (yearName === '') {
                errorLabel.textContent = "Please select a year level.";
                return;
            }

            // Get all existing year names from the table for validation
            const existingYears = document.querySelectorAll("#groupDataTable tbody tr");

            if (!existingYears || existingYears.length === 0) {
                return; // No records to check, so exit early
            }

            for (let row of existingYears) {
                if (!row.cells || row.cells.length < 2) continue; // Skip invalid rows

                const yearId = row.cells[0].textContent.trim(); // Hidden ID
                const yearCaption = row.cells[1].textContent.trim().toLowerCase(); // Year name
                
                // Check for duplicate when creating a new entry
                if (action === 'create' && yearCaption === yearText.toLowerCase()) {
                    errorLabel.textContent = "This year level already exists! Please enter a different year.";
                    return;
                }

                // Check for duplicate in edit (Ignore same row)
                if (action === 'edit' && yearCaption === yearText.toLowerCase() && yearId !== selectedYearId) {
                    errorLabel.textContent = "This year already exists under another entry!";
                    return;
                }
            }


             // Proceed with saving the form via AJAX
            const formData = new FormData(form);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.onload = function () {
                location.reload();
            };
            xhr.send(formData);
        }



        function deleteYear(id) {
            if (confirm('Are you sure you want to delete this year?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '', true);
                xhr.onload = function () {
                        location.reload();
                 };
                xhr.send(formData);
            }
        }
        $(document).ready(function() {
            $('#groupDataTable').DataTable({
                "ordering": false,
                "language": {
                    "emptyTable": "No year level records found!" // Change this text as needed
                }
            });
        });
    </script>
</body>
<style>
body
{
    background:transparent;
}

</style>
</html>
