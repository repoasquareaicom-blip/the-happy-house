<?php
session_start();
include 'config/data.php';
include 'class/stripe.php';
include 'class/log.php';
include 'class/subscription.php';
include 'objects/schooldata.php';

$stripeConfig = require 'config/stripe.php';
$stripeMode   = $stripeConfig['mode'] ?? 'live';
$stripePk     = $stripeConfig[$stripeMode]['publishable_key'] ?? '';


// dynamic css
include 'assets/css/pages/dynamicss.php';

$login_url = "school_admin_login.php";
if(isset($_SESSION['school_admin_login_status'])){
	if($_SESSION['school_admin_login_status'] !=  "true"){
		header("Location: $login_url");	
	}
}
else{
	header("Location: $login_url");	
}
$_data = new Data();
$subscription = new Subscription($_data);
$_subscription_data = $subscription->get_user_by_email($_SESSION['school_email']);

$_start_date="";
$_end_date="";
$_cancel_at="";
$_curr_cancel_at = "";
$_cancel_at_period_end="";
$_status="";
$_school_id="";
$_games_subscription_id="";
$_curriculum_subscription_id="";
foreach ($_subscription_data as $_subscription_details) {
    $_games_subscription_id = $_subscription_details['subscription_id'];
    $_curriculum_subscription_id = $_subscription_details['curriculum_sub_id'];
    $_start_date=$_subscription_details['subscription_start'];
    $_end_date=$_subscription_details['subscription_end'];
    $_cancel_at=$_subscription_details['cancel_at'];
    $_cancel_at_period_end=$_subscription_details['cancel_at_period_end'];
    $_status=$_subscription_details['status'];

    $curr_start = $_subscription_details['curriculum_start'];
    $curr_end   = $_subscription_details['curriculum_end'];
    $curr_status = $_subscription_details['curriculum_status'] ?? 'inactive';
    $_curr_cancel_at = $_subscription_details['curriculum_cancel_at'] ?? '';
    $_curr_cancel_at_period_end = $_subscription_details['curriculum_cancel_at_period_end'] ?? '0';

    $_SESSION['stripe_customer_id'] = $_subscription_details['customer_id']; // Games
    $_SESSION['school_admin_email'] = $_subscription_details['school_admin_email']; // Games
    
    

    $current_time = time();
    $raw_end_date = $_subscription_details['subscription_end'] ?? '1970-01-01'; 
    $expiry_time = is_numeric($raw_end_date) ? $raw_end_date : strtotime($raw_end_date);

    if ($expiry_time > 0 && $current_time < $expiry_time) {
        $_status = 'active';
    } else {
        $_status = 'expired';
    }
    if (empty($_games_subscription_id)) {
        $_games_status_label = 'NOT SUBSCRIBED';
        $_games_status_description_label = "You’re not subscribed. Subscribe now to access Wellbeing Games.";
        $_games_red_ribbon_description_label = "You’re not subscribed. Subscribe now to access Wellbeing Games.";
    } elseif ($current_time > $expiry_time) {
        $_games_status_description_label = "Your subscription has ended. Re-subscribe to continue enjoying Wellbeing Games.";
        $_games_status_label = 'EXPIRED / IN-ACTIVE';
        $_games_red_ribbon_description_label = "Your subscription has ended. Re-subscribe to continue enjoying Wellbeing Games.";
    } else {
        $_games_status_label = 'ACTIVE';
        $_games_status_description_label = "";
        $_games_red_ribbon_description_label = "";
    }
    $raw_curr_end = $_subscription_details['curriculum_end'] ?? '1970-01-01';
    $curriculum_expiry_time = is_numeric($raw_curr_end) ? $raw_curr_end : strtotime($raw_curr_end);

    if ($curriculum_expiry_time > 0 && $current_time < $curriculum_expiry_time) {
        $curr_status = 'active';
    } else {
        $curr_status = 'expired';
    }
    if (empty($_curriculum_subscription_id)) {
        $_curriculum_status_label = 'NOT SUBSCRIBED';
        $_curriculum_status_description_label = "Subscribe to unlock the Curriculum Program subscription.";
        $_curriculum_red_ribbon_description_label = "Subscribe to unlock the Curriculum Program subscription.";
    } elseif ($current_time > $curriculum_expiry_time) {
        $_curriculum_status_label = 'EXPIRED / IN-ACTIVE';
        $_curriculum_status_description_label = "Your subscription has ended. Re-subscribe to regain access to the Curriculum Program subscription.";
        $_curriculum_red_ribbon_description_label = "Your subscription has ended. Re-subscribe to regain access to the Curriculum Program subscription.";
    } else {
        $_curriculum_status_label = 'ACTIVE';
        $_curriculum_status_description_label = "";
        $_curriculum_red_ribbon_description_label = "";
    }

    $_school_name = $_subscription_details["school_name"];
    $_school_contact_name = $_subscription_details["name"];
    $_school_email = $_subscription_details["school_admin_email"];
    $_school_id = $_subscription_details["id"];
    $_SESSION['school_id']  = $_school_id;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/pages/dashboard.css">
    <link rel="stylesheet" href="assets/css/global.css">
    
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- Bootstrap Tooltip Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.min.js"></script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.min.js"></script>

    <!-- Favicons -->
<link rel="icon" href="./favicons/favicon.ico" sizes="any" />
<link rel="icon" href="./favicons/icon.svg" type="image/svg+xml" />
<link rel="apple-touch-icon" href="./favicons/apple-touch-icon.png" />
<link rel="manifest" href="./favicons/manifest.webmanifest" />

  <!--  Essential META Tags -->
	<meta property="og:title" content="The Happy House ">
	<meta property="og:type" content="website" />
	<meta property="og:image" content="https://thehappyhouse.au/dev/assets/images/The-Happy-House-Logo.svg">
	<meta property="og:url" content="https://thehappyhouse.au">
<style>
    body
    {
        font-family: Calibri, sans-serif;
    }
   .fixed-tips-btn {
        position: fixed;
        bottom: 20px;
        right: 70px;
        z-index: 10001; /* Keeps it above iframes and data tables */
    }
    #tips-button
    {
        background-color: rgb(213,163,152); /* Matches your theme */
        color: white;
        padding: 12px 24px;
        border-radius: 50px;
        text-decoration: none;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        border: 2px solid rgba(255,255,255,0.2);
        float:right;
        color:#000;
        padding-left:50px;
        padding-right:50px;

    }

    .fixed-tips-btn i {
        font-size: 1.1em;
    }

    #tips-button:hover {
        background-color: rgb(50,134, 169);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        color: #f0f0f0;
    }

    #tips-button:active {
        transform: translateY(0);
    }

    /* Optional: Hide it if the screen is too small to avoid overlapping UI */
    @media (max-width: 480px) {
        .fixed-tips-btn {
            padding: 10px 15px;
            font-size: 0.9em;
            bottom: 10px;
            right: 10px;
        }
    }

    #subscriptionInfoContainer {
        display: flex;
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid #d1d9e0;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .sub-column {
        flex: 1;
        display: flex;
        flex-direction: column;
        border-right: 1px solid #edf2f7;
    }

    .sub-column:last-child { border-right: none; }

    .sub-header {
        background-color: #4C7AA2;
        color: #fff;
        padding: 10px 15px;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-transform: uppercase;
    }

    .sub-body {
        padding: 12px 15px;
        background: #fcfdfe;
        flex-grow: 1;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 4px;
        align-items: center;
    }

    .info-label {
        font-size: 0.65rem;
        color: #718096;
        font-weight: 600;
    }

    .info-value {
        font-size: 0.8rem;
        color: #2d3748;
        font-weight: 700;
    }

    .status-badge {
        font-size: 0.65rem;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 800;
    }

    .status-active { background: #e6fffa; color: #234e52; border: 1px solid #b2f5ea; }
    .status-expired { background: #fff5f5; color: #9b2c2c; border: 1px solid #feb2b2; }

    .text-danger-custom { color: #e53e3e !important; }
    .text-success-custom { color: #4C7AA2 !important; }
</style>    
</head>
<body>
    <div class="dashboard-container">
        <section class="banner-background">
        <section class="banner">
            <img src="assets/images/banner-bg.svg" alt="Banner Background" class="banner-image" onclick="changeImage(this,'banner')" style="display: block;">
        </section>
    </section>
        <!-- Header -->
        <header class="responsive-header">
            <img src="assets/images/The-Happy-House-Logo.svg" alt="Logo" class="logo">
            <div class="title"><?php echo $_SESSION['school_name'] ?></div>
            <div class="action-icons">
                <!-- <button class="icon-btn"><i class="fas fa-cog"></i></button> -->
                <button class="icon-btn"><i class="fas fa-sign-out-alt" onclick="window.location.href='school_admin_logout.php';"></i></button>
            </div>
        </header>

        <!-- Content Section -->
        <div class="content-container">
    
    <?php if ($_status === 'active'): ?>
        <main class="right-section">
            <div class="actions-row" id="actionRow">
                <div class="action-box" id="mananageYearLevelGroups">
                    <p>Manage Year Levels</p>
                    <i class="fas fa-cogs action-icon"></i>
                </div>
                <div class="action-box" id="manageClassLevelGroups">
                    <p>Manage Classes </p>
                    <i class="fas fa-chalkboard-teacher action-icon"></i>
                </div>
                <div class="action-box" id="gameResults">
                    <p>Wellbeging Results + Game Score Ressults </p>
                    <i class="fas fa-trophy action-icon"></i>
                </div>
                <div style="margin-bottom: 25px;">
                    <button class="btn-start-wellbeing-games" id="btnStartWellbeingGames">Start Wellbeing Games</button>
                </div>    
                <p>Getting Started Guide - Watch the Help Video below to learn how to start The Happy House Wellbeing Activities</p>
                <iframe width="560" height="315" src="https://www.youtube.com/embed/_XH1CQOYirY?si=HM3wOr6w2XIJCAOX" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            
            <div id="dataTableDiv">
                <div id="goBack">
                    <i class="fas fa-arrow-left"></i> Go Back
                </div>
                <div id="dataTitleText"></div>
                <div id="daTableRecords">
                    <iframe id="iframeManageData" frameborder="0" style="width: 100%; height: 100%;"></iframe>
                </div>
            </div>
        </main>
    <?php endif; ?>

    <aside class="left-section" style="<?php echo ($_status !== 'active') ? 'width: 100%; max-width: 100%; flex: 1;' : ''; ?>">
        
        <div id="subscriptionInfoContainer" style="<?php echo ($_status !== 'active') ? 'display: flex; gap: 20px; flex-wrap: wrap;' : ''; ?>">
    
            <div class="sub-column" style="<?php echo ($_status !== 'active') ? 'flex: 1; min-width: 300px;' : ''; ?>">
                <div class="sub-header">
                    <span><i class="fas fa-gamepad mr-1"></i> Wellbeing Games</span>
                    <span class="status-badge <?php echo ($_status === 'active') ? 'status-active' : 'status-expired'; ?>">
                        <?php echo $_games_status_label ?>
                    </span>
                </div>
                <div class="sub-body">
                    <?php if (!empty($_start_date)) : ?>
                    <div class="info-row">
                        <span class="info-label">PERIOD</span>
                        <span class="info-value"><?php echo date('d M y', strtotime($_start_date)); ?> - <?php echo date('d M y', strtotime($_end_date)); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">RENEWAL</span>
                        <span class="info-value <?php echo ($_cancel_at_period_end == "1") ? 'text-danger-custom' : 'text-success-custom'; ?>">
                            <?php echo ($_cancel_at_period_end == "1") ? "Stopped" : "Auto-Renew"; ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($_cancel_at)) : ?>
                    <div class="info-row">
                        <span class="info-label">CANCEL AT</span>
                        <span class="info-value small text-muted"><?php echo htmlspecialchars($_cancel_at); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php else : ?>
                        <div style="text-align: center; padding-top: 10px;">
                            <i class="fas fa-lock text-muted" style="font-size: 1rem;"></i><br>
                            <span style="font-size: 0.7rem; color: #3d495a;"><?php echo $_games_status_description_label ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sub-column" style="<?php echo ($_status !== 'active') ? 'flex: 1; min-width: 300px;' : ''; ?>">
                <div class="sub-header" style="filter: brightness(1.15);">
                    <span><i class="fas fa-book-open mr-1"></i> Learning Curriculum Program</span>
                    <span class="status-badge <?php echo ($curr_status === 'active') ? 'status-active' : 'status-expired'; ?>">
                        <?php echo $_curriculum_status_label ?>
                    </span>
                </div>
                <div class="sub-body">
                    <?php if (!empty($curr_start)) : ?>
                        <div class="info-row">
                            <span class="info-label">PERIOD</span>
                            <span class="info-value"><?php echo date('d M y', strtotime($curr_start)); ?> - <?php echo date('d M y', strtotime($curr_end)); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">RENEWAL</span>
                            <span class="info-value <?php echo ($_curr_cancel_at_period_end == "1") ? 'text-danger-custom' : 'text-success-custom'; ?>">
                                <?php echo ($_curr_cancel_at_period_end == "1") ? "Stopped" : "Auto-Renew"; ?>
                            </span>
                        </div>
                         <?php if (!empty($_curr_cancel_at)) : ?>
                        <div class="info-row">
                            <span class="info-label">CANCEL AT</span>
                            <span class="info-value small text-muted"><?php echo htmlspecialchars($_curr_cancel_at); ?></span>
                        </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div style="text-align: center; padding-top: 10px;">
                            <i class="fas fa-lock text-muted" style="font-size: 1rem;"></i><br>
                            <span style="font-size: 0.7rem; color: #3d495a;"><?php echo $_curriculum_status_description_label ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <nav class="nav-links">
            <a href="#" id="editProfileBtn" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
            <a href="manage_subscriptions.php">
                <i class="fas fa-user-cog"></i> Manage Wellbeing/Games or Curriculum Subscriptions
            </a>

            <?php if ($curr_status === 'active'): ?>
                <a href="curriculum.php"  class="d-flex align-items-center">
                    <span><i class="fas fa-book-reader"></i> Manage Curriculum Learning Program</span>
                    <button type="button" class="btn-start-wellbeing-games" style="margin-left:10px;" id="btnAccessCurriculum">
                        Access Curriculum
                    </button>
                </a>
            <?php endif; ?>
        </nav>
    </aside>
</div>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2025 The Happy House. All rights reserved.</p>
        </footer>
    </div>

    
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editProfileForm">
          <div class="mb-3">
            <label for="schoolName" class="form-label">School Name</label>
            <input type="text" id="schoolName" name="schoolName" class="form-control" value="<?php echo $_school_name; ?>" required>
          </div>
          <div class="mb-3">
            <label for="emailID" class="form-label">Email ID (Login ID)</label>
            <input type="email" id="emailID" name="emailID" class="form-control" value="<?php echo $_school_email; ?>" required>
            <input type="hidden" id="schoolID" name="schooLID" class="form-control" value="<?php echo $_school_id;?>">
            
          </div>
          <div class="mb-3">
            <label for="contactPersonName" class="form-label">Contact Person Name</label>
            <input type="text" id="contactPersonName" name="contactPersonName" class="form-control" value="<?php echo $_school_contact_name; ?>" required>
          </div>
          <button type="submit" class="logo-theme-button">Submit</button>
          <div id="validation-message"></div>

        </form>
      </div>
    </div>
  </div>
</div>

<!-- Loader (hidden by default) -->
<div id="loader">
    <div class="spinner"></div>
</div>
<div id="banner-stack-container">
    <?php if ($_status === 'expired'): ?>
    <div class="subscription-alert-banner">
        <div class="banner-content-wrapper">
            <span class="banner-text">
                <strong>Wellbeing Games:</strong> <?php echo $_games_red_ribbon_description_label ?>. Why not subscribe?
            </span>
            <button class="btn-re-subscribe resub-trigger" data-product="wellbeing_games">
                SUBSCRIBE NOW
            </button>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($curr_status === 'expired'): ?>
    <div class="subscription-alert-banner">
        <div class="banner-content-wrapper">
            <span class="banner-text">
                <strong>Learning Curriculum Program:</strong> <?php echo $_curriculum_red_ribbon_description_label ?>
            </span>
            <button class="btn-re-subscribe resub-trigger" data-product="curriculum">
                SUBSCRIBE NOW
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="fixed-tips-btn">
    <a href="helpful-tips.html" id="tips-button" class="btn btn-warning shadow-sm">
        <i class="fas fa-lightbulb"></i> Helpful Tips
    </a>
</div>

</body>
<script>
$(document).ready(function() {
    // Action for Manage Year Level Groups
    $('#mananageYearLevelGroups').click(function() {
        $('#iframeManageData').attr('src', 'manage_year_group.php');
        $('#dataTableDiv').show();
        $('#actionRow').hide();
        const iframe = $('#iframeManageData');
        iframe.css('height', `calc(100vh - 100px)`);
    });
    $('#manageClassLevelGroups').click(function() {
        $('#iframeManageData').attr('src', 'manage_class_group.php');
        $('#dataTableDiv').show();
        $('#actionRow').hide();
        const iframe = $('#iframeManageData');
        iframe.css('height', `calc(100vh - 100px)`);
    });
    $('#btnStartWellbeingGames').click(function() {
        $('#iframeManageData').attr('src', 'manage_class_group.php');
        $('#dataTableDiv').show();
        $('#actionRow').hide();
        const iframe = $('#iframeManageData');
        iframe.css('height', `calc(100vh - 100px)`);
    });
    
    $('#gameResults').click(function() {
        $('#iframeManageData').attr('src', 'reports/report.php');
        $('#dataTableDiv').show();
        $('#actionRow').hide();
        const iframe = $('#iframeManageData');
        iframe.css('height', `calc(100vh - 100px)`);
    });
    // Go back action
    $('#goBack').click(function() {
        $('#dataTableDiv').hide();
        $('#actionRow').show();
    });
});

// Trigger the modal on "Edit Profile" click
$(document).ready(function () {
    $('#editProfileBtn').click(function (e) {
        e.preventDefault(); // Prevent default link action
        $('#validation-message').html('<div></div>');
        $('#editProfileModal').modal('show'); // Show the modal
    });

    // Handle the form submission
    $('#editProfileForm').submit(function (e) {
        e.preventDefault(); // Prevent form from refreshing the page
        $('#loader').show();
        // Retrieve form data
        const schoolName = $('#schoolName').val();
        const emailID = $('#emailID').val();
        const contactPersonName = $('#contactPersonName').val();
        const schoolID = $('#schoolID').val();
        
        // Submit data via AJAX to a PHP handler (update_profile.php)
        $.ajax({
            url: 'AJAX.php', // PHP handler for updating the profile
            method: 'POST',
            data: {
                schoolName: schoolName,
                contactPersonName: contactPersonName,
                emailID: emailID,
                schoolId: schoolID,
                method:'updateProfile'
            },
            success: function (response) {
                $('#loader').hide();
                $('#validation-message').html('<div>Updated successfully!</div>');
                $('.title').text($('#schoolName').val());
            },
            error: function (xhr, status, error) {
                $('#loader').hide();
                // Log the full response for debugging
                console.error("Error response:", xhr.responseText);
                console.error("Status:", status);
                console.error("Error:", error);

                // Alert the error message to the user (optional)
                alert('Failed to update profile. Error: ' + xhr.responseText);
        }
        });
    });
});


</script>
<style>
    .modal-header {
    background-color: #4C7AA2;
    color: white;
}

.modal-title {
    font-weight: bold;
}

  /* Center modal vertically and horizontally */
  .modal-dialog {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh; /* Ensures it takes up full screen height */
  }

  /* Style for input text boxes */
  .form-control {
    background-color: #f8f9fa; /* Light gray color */
    color: #495057; /* Darker text color */
    border: 2px solid #ced4da;
    border-radius: 5px;
  }

  .form-control:focus {
    background-color: #f8f9fa;
    color: #495057;
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    border: 2px solid #007bff;
  }

  /* Center the button */
  .logo-theme-button {
    display: block;
    margin: 1rem auto; /* Center button horizontally */
    background-color: #b77c72; /* Bootstrap primary button color */
    color: #fff;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
  }

  .logo-theme-button:hover {
    background-color: #8f5f58; /* Darker blue on hover */
  }

  /* Loader styles */
#loader {
    display: none; /* Initially hidden */
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999; /* Ensure it appears above all other content */
    text-align: center;
}

#loader .spinner {
    border: 4px solid rgba(255, 255, 255, 0.3); /* Light border */
    border-top: 4px solid #3498db; /* Blue top border */
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite; /* Animation for spinning */
}

/* Keyframes for the spinning animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.btn-start-wellbeing-games {
    background-color: rgb(69, 175, 85);
    color: black;
    border-radius: 25px;
    padding: 10px;
    padding-left: 30px;
    padding-right: 30px;
    border: none;
    font-size: 1.2em;
    /* Add for smooth transition */
    transition: transform 0.3s ease-in-out;
    /* Ensure it behaves like an inline-block for transform to work correctly */
    display: inline-block;
    cursor: pointer; /* Indicate it's clickable */
    text-decoration: none; /* Remove underline if it's an <a> tag */
}

.btn-start-wellbeing-games:hover {
    /* Slight zoom effect */
    transform: scale(1.05); /* Zooms in by 5% */
}
/* Container to hold and stack multiple banners at the bottom */
/* Container for stacking */
#banner-stack-container {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    z-index: 9999;
    display: flex;
    flex-direction: column-reverse;
    pointer-events: none;
}

.subscription-alert-banner {
    pointer-events: auto;
    width: 100%;
    background-color: #dc3545;
    color: white;
    padding: 12px 20px;
    margin-bottom: 2px; /* Small gap between red bars */
    box-shadow: 0 -2px 10px rgba(0,0,0,0.2);
    display: flex;
    justify-content: center; /* Centers everything horizontally */
    align-items: center;
    animation: slideUp 0.5s ease-out;
}

.banner-content-wrapper {
    display: flex;
    justify-content: center; /* Keeps text and button together in the center */
    align-items: center;
    gap: 20px; /* Adjust this to bring them closer or further apart */
    max-width: 1200px;
    width: 100%;
}

.banner-text {
    font-size: 15px;
    font-weight: 500;
}

.btn-re-subscribe {
    background-color: white;
    color: #dc3545 !important;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 12px;
    padding: 6px 16px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    white-space: nowrap;
}


/* Remove margin for the very bottom-most bar */
.subscription-alert-banner:first-child {
    margin-bottom: 0;
}

@keyframes slideUp {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}



.btn-re-subscribe {
    background-color: white;
    color: #dc3545 !important;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 13px;
    padding: 8px 20px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    white-space: nowrap;
    transition: background-color 0.2s;
}

.btn-re-subscribe:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .banner-content-wrapper {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    .banner-text { text-align: center; }
}
</style>

 <script src="https://js.stripe.com/v3/"></script>
<script>

var stripe = Stripe("<?= htmlspecialchars($stripePk) ?>");

document.getElementById('reSubscribeBtn').addEventListener('click', function (e) {
    e.preventDefault();
 fetch('checkout_resubscribe.php')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                return stripe.redirectToCheckout({ sessionId: data.id });
            })
            .then(result => {
                if (result && result.error) {
                    alert(result.error.message);
                }
            });
    });



</script>

<script>
$(document).ready(function() {
    $('.resub-trigger').on('click', function(e) {
        e.preventDefault();
        
        const btn = $(this);
        const productType = btn.data('product'); // Gets 'wellbeing_games' or 'curriculum'
        
        // 1. Show loading state
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        // 2. Redirect to your resubscription PHP script
        // This sends the type to the script we discussed earlier
        window.location.href = 'create_resubscription.php?type=' + productType;
    });
});
</script>

</html>
