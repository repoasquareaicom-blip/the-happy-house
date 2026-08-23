<?php
session_start();
// Retrieve the date and then clear it so it doesn't persist forever
$display_date = $_SESSION['temp_expiry_date'] ?? "the scheduled end date";
unset($_SESSION['temp_expiry_date']); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <style>
        body {
            background: url("assets/images/bg_game_settings.jpeg") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }
        .expired-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
            border: 3px solid #f44336;
        }
        h1 { color: #d32f2f; }
        .date-highlight {
            font-weight: bold;
            background: #ffeb3b;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .btn-back {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 25px;
            background: #856404;
            color: white;
            text-decoration: none;
            border-radius: 50px;
        }
    </style>
</head>
<body>

    <div class="expired-box">
        <div style="font-size: 50px;">⚠️</div>
        <h1>Access Expired</h1>
        <p>
            This school subscription expired on 
            <span class="date-highlight"><?php echo htmlspecialchars($display_date); ?></span>.
        </p>
        <p>Please contact your school administrator to renew the account access.</p>
        
        <a href="index.php" class="btn-back">Return to Home</a>
    </div>

</body>
</html>