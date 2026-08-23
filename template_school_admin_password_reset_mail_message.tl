
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            background-color: #8f5f58;
            color: white;
            padding: 10px;
            border-radius: 6px 6px 0 0;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .content p {
            font-size: 16px;
            color: #333;
            line-height: 1.6;
        }
        .btn-reset {
            display: inline-block;
            margin: 20px 0;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #8f5f58;
            color: #fff!important;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-reset:hover {
            background-color: #8f5f58;
        }
        .footer {
            font-size: 12px;
            color: #999;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
    <div style="flex-shrink: 0; text-align: center; min-width: 200px;">
    <img src='cid:$cid' alt='The Happy House Logo' alt="The Happy House Logo" style="max-width: 200px; height: auto;">
    </div>
        <div class="header">
            <h1>Reset Your Password</h1>
        </div>
        <div class="content">
            <p>Hi {{username}},</p>
            <p>We received a request to reset your password. Click the button below to reset it. If you didn’t request this, you can safely ignore this email.</p>
            <a href="{{reset_link}}" class="btn-reset">Reset Password</a>
            <p>This link will expire in {{expiry_time}} minutes.</p>
        </div>
        <div class="footer">
            <p>If you’re having trouble clicking the button, copy and paste the link below into your web browser:</p>
            <p>{{reset_link}}</p>
        </div>
    </div>
</body>
</html>

