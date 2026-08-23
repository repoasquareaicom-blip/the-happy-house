<?php
session_start();
include 'config/data.php';
include 'class/log.php';
include 'class/class_manage_groups.php';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

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
$group = new Groups($_data);

$years = $group->fetch_all_year_groups($_SESSION['school_id']);
//$selected_year_id = $_GET['year_id'] ?? ($years[0]['id'] ?? NULL);
$selected_year_id = isset($_GET['year_id']) ? $_GET['year_id'] : NULL;


// Handle form submissions for adding, editing, or deleting class groups
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'edit') {
        // Add or Edit Class
        $class_name = $_POST['txt_class_group_caption'] ?? '';
        $password = $_POST['password'] ?? '';  // Optional password field
        $year_id = $_POST['year_id'] ?? '';

        if (empty($class_name) || empty($year_id) || empty($password)) {
            echo "Class Name, Year and Password are required!";
            exit;
        }

        if ($action === 'create') {
            // Create new class
            $result = $group->add_class_group($_SESSION['school_id'],$class_name, $year_id, $password);

        } elseif ($action === 'edit') {
            // Edit existing class
            $class_id = $_POST['selected_class_group_id'] ?? '';
            if ($class_id) {
                $result = $group->edit_class_group($class_id, $class_name, $password);
            }
        }
    } elseif ($action === 'delete') {
        // Delete class
        $class_id = $_POST['id'] ?? '';
        if ($class_id) {
            $result = $group->delete_class_group($class_id);
            if ($result === "OK") {
                echo "Class deleted successfully!";
            } else {
                echo "Failed to delete class!";
            }
        }
    }
}

$classes = $selected_year_id ? $group->fetch_all_class_groups_by_year($_SESSION['school_id'], $selected_year_id) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Class Groups</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/pages/dashboard.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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
<body style="background:transparent">
    <h1>Manage Class Groups</h1>

    
    <div class="d-flex align-items-center gap-2 flex-wrap">
    <select name="year-selector" id="year-selector" 
        class="form-select form-select-lg bg-success text-white border border-primary rounded-1 p-2"
        required onchange="filterByYear()" 
        style="max-width: 250px; width: 100%;">

        <option value="" disabled <?= is_null($selected_year_id) ? 'selected' : '' ?> style="background-color: white; color: black;">
        Select Year Level
        </option>

    <?php if (!empty($years)): ?>
        <?php foreach ($years as $year): ?>
            <option value="<?= $year['id'] ?>" <?= (isset($selected_year_id) && $year['id'] == $selected_year_id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($year['year_group_caption']) ?>
            </option>
        <?php endforeach; ?>
    <?php else: ?>
        <option value="" disabled style="background-color: white; color: black;">No available year levels</option>
    <?php endif; ?>
</select>




        <button onclick="openModal('create')" class="logo-theme-button" id="addClassButton" >Add New Class</button>

    </div>


    

    <table id="classDataTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="display:none">ID</th>
                <th>Class Name</th>
                <th>Password</th>
                <th>Teacher's Login URL (Tap to copy)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($classes as $class): ?>
                <tr>
                    <td style="display:none"><?= htmlspecialchars($class['id']) ?></td>
                    <td><?= htmlspecialchars($class['class_group_caption']) ?></td>
                    <td><?= htmlspecialchars($class['password']) ?></td> <!-- Display Password -->
                    <td style="cursor:pointer" onclick="copyToClipboard(this)" 
                        data-url="<?= $protocol . '://' . $_SERVER['HTTP_HOST'] . "/teachers_login_request_handler.php?classroomref=" . htmlspecialchars($class['guid']) ?>"
                        class="copy-cell">
                        <?= $protocol . '://' . $_SERVER['HTTP_HOST'] . "/teachers_login_request_handler.php?classroomref=" . htmlspecialchars($class['guid']) ?>
                    </td>
                    <td style="white-space: nowrap;">
                        <div style="display: flex; gap: 5px;">
                            <button class="btn btn-primary btn-sm" onclick="openModal('edit', <?= htmlspecialchars(json_encode($class)) ?>)">
                                Edit
                            </button>
                            
                            <button class="btn btn-danger btn-sm" onclick="deleteClass(<?= htmlspecialchars($class['id']) ?>)">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>

            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Modal for Create/Edit Class -->
    <div id="class-modal" class="modal">
        <div class="modal-content">
            
            <div class="modal-header">
                <h2 id="modal-title">Create/Edit Class</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>

            <form id="class-form" method="POST" style="width: 100%; padding: 15px;">
                <input type="hidden" name="action" id="form-action">
                <input type="hidden" name="selected_class_group_id" id="class-id">
                <input type="hidden" name="year_id" id="year-id" value="<?= $selected_year_id ?>">

                <div style="width: 100%; margin-bottom: 15px;">
                    <label for="year-label" style="display: block; margin-bottom: 5px;">Selected Year Level : <b><?= htmlspecialchars($group->fetch_year_group_by_id($selected_year_id)[0]['year_group_caption'] ?? '') ?></b></label>
                </div>
                <div style="width: 100%; margin-bottom: 15px;">
                    <label for="class-name" style="display: block; margin-bottom: 5px;">Class Name:</label>
                    <input type="text" name="txt_class_group_caption" id="class-name" placeholder="Class Name" required 
                        style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
                </div>

                <div style="width: 100%; margin-bottom: 15px;">
                    <label for="password" style="display: block; margin-bottom: 5px;">Password:</label>
                    <input type="password" name="password" id="password" placeholder="Password" 
                        style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
                </div>

              

                <div style="text-align: right;">
                    <button type="submit" class="logo-theme-button" style="padding: 10px 20px; border: none; background-color: #28a745; color: white; border-radius: 5px; cursor: pointer;">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>


    <script>
        const modal = document.getElementById('class-modal');

        // Open modal for create/edit
        function openModal(action, classData = null) {
            
            document.getElementById('form-action').value = action;
            document.getElementById('modal-title').innerText = action === 'edit' ? 'Edit Class' : 'Create Class';
            document.getElementById('class-id').value = classData ? classData.id : '';
            document.getElementById('class-name').value = classData ? classData.class_group_caption : '';
            document.getElementById('password').value = '';  // Reset password field
            modal.style.display = 'block';  // Show modal
        }

        // Close modal
        function closeModal() {
            modal.style.display = 'none';  // Hide modal
        }

        // Filter by Year
        function filterByYear() {
            
            const yearId = document.getElementById('year-selector').value;
            window.location.href = `manage_class_group.php?year_id=${yearId}`;
        }

        // Delete class
        function deleteClass(id) {
            if (confirm('Are you sure you want to delete this class?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '', true);
                xhr.onload = function () {
                    location.reload();  // Reload page after deletion
                };
                xhr.send(formData);
            }
        }

        // Initialize DataTables
        $(document).ready(function() {
            $('#classDataTable').DataTable(
            {
                "language": {
                    "emptyTable": "No class group records found!" // Change this text as needed
                }
            });
       
        });

        // Close the modal if clicked outside of it
        window.onclick = function (event) {
            if (event.target === modal) {
                closeModal();
            }
        };

        function copyToClipboard(cell) {
            const text = cell.getAttribute("data-url");
            navigator.clipboard.writeText(text).then(() => {
                showToast("Copied to clipboard: " + text);
            }).catch(err => {
                console.error("Failed to copy: ", err);
            });
        }

        function showToast(message) {
            const toast = document.createElement("div");
            toast.textContent = message;
            toast.className = "toast-message";
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add("fade-out");
            }, 1500);

            setTimeout(() => {
                toast.remove();
            }, 2000);
        }


    </script>
</body>
<style>
    .toast-message {
    background: #333;
    color: rgba(252, 252, 252, 0.8);
    padding:10px;
    border-radius: 5px;
    font-size: 14px;
    opacity: 1;
    transition: opacity 0.5s;
    width:auto;
}


.toast-message.fade-out {
    opacity: 0;
}
</style>
</html>
