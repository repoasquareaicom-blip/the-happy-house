<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Calibri, Arial, sans-serif; line-height: 1.6; color: #333;">
  <div style="
        max-width: 600px; 
        margin: 0 auto; 
        padding: 20px; 
        border: 1px solid #ddd; 
        border-radius: 5px; 
        display: flex; 
        flex-wrap: wrap; 
        align-items: center; 
      ">
    <div style="flex: 1; padding-right: 20px; min-width: 300px;">
      <h2 style="color: rgb(0, 30, 62);">Welcome to The Happy House!</h2>
      <p>Thank you for choosing to support wellbeing and learning.</p>
      
      <p>To continue with your subscription, please verify your email using this code:</p>
      <p style="text-align: center;">
        <strong style="font-size: 2em; color: #b77c72; letter-spacing: 5px;">{{otp}}</strong>
      </p>
      
      <p>This code is valid for the next <strong>{{expiry_time}}</strong> minutes. Once verified, you will proceed to secure payment.</p>
      
      <p>If you did not start this process, you can safely ignore this email.</p>
      
      <p style="margin-top: 20px;">Best regards,</p>
      <p style="font-weight: bold;">The Happy House Team</p>
    </div>

    <div style="flex-shrink: 0; text-align: center; min-width: 200px;">
     <img src='cid:$cid' alt='The Happy House Logo' style="max-width: 200px; height: auto;">
    </div>
  </div>

  <style>
    @media (max-width: 600px) {
      div[style*="display: flex"] {
        flex-direction: column;
        text-align: center;
      }
      div[style*="min-width: 200px"] {
        order: -1; /* Move logo to top on mobile */
        margin-bottom: 20px;
      }
      div[style*="flex: 1"] {
        padding-right: 0;
      }
      p {
        text-align: left;
      }
    }
  </style>
</body>
</html>