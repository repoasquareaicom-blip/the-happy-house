<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
  <div style="max-width: 600px; margin: 20px auto; padding: 25px; border: 1px solid #e1e1e1; border-radius: 12px; background-color: #ffffff;">
    
    <div style="text-align: center; margin-bottom: 25px;">
       <img src='cid:$cid' alt='The Happy House Logo' style="max-width: 150px; height: auto;">
    </div>

    <div style="padding: 0 10px;">
      <h2 style="color: #001e3e; text-align: center; margin-bottom: 20px;">Payment Successful!</h2>
      
      <p>Dear School Administrator,</p>
      
      <p style="font-size: 1.1em; color: #444; background-color: #f9f9f9; padding: 15px; border-left: 4px solid #b77c72;">
        <strong>Welcome to {{product_name}}. Thanks for subscribing!</strong>
      </p>
      
      <p style="margin-top: 20px;">Your payment has been processed successfully. To complete your subscription setup and gain access to your dashboard, please click the button below to finalize your school profile.</p>

      <div style="text-align: center; margin: 35px 0;">
        <a href="{{finish_url}}" style="
            background-color: #b77c72; 
            color: #ffffff; 
            padding: 18px 35px; 
            text-decoration: none; 
            border-radius: 8px; 
            font-weight: bold; 
            display: inline-block;
            font-size: 1.1em;
        ">Complete Your Signup</a>
      </div>

      <p style="font-size: 0.85em; color: #888; border-top: 1px solid #eee; padding-top: 20px;">
        If you have any issues with the button, please copy and paste this URL into your browser:<br>
        <span style="color: #b77c72;">{{finish_url}}</span>
      </p>

      <p style="margin-top: 30px; font-size: 0.9em;">
        Best regards,<br>
        <strong>The Happy House Team</strong>
      </p>
    </div>
  </div>
</body>
</html>