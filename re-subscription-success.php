<?php
session_start();    // Join the current session
session_unset();    // Remove all variables (like school_id, subscription_id)
session_destroy();  // Delete the session file from the server
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Successful</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .success-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .icon-circle {
            width: 70px;
            height: 70px;
            background-color: #dcfce7;
            color: #166534;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        h1 {
            color: #1e293b;
            font-size: 24px;
            margin-bottom: 10px;
        }
        p {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>

    <div class="success-card">
        <div class="icon-circle">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        
        <h1>Payment Successful!</h1>
        <p>Your subscription has been successfully updated. You can now return to your dashboard to continue managing your school.</p>
        
        <a href="school_admin_login.php" class="btn">
            Login to Dashboard
        </a>
    </div>

</body>
</html>