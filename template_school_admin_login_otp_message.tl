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
    <!-- Left Content -->
    <div style="flex: 1; padding-right: 20px; min-width: 300px;">
      <h2 style="color: rgb(0, 30, 62);">Your One-Time Code for School Admin Login</h2>
      <p>Dear <strong>{{username}}</strong>,</p>
      <p>Your One-Time Password (One-Time Code) for login is: <strong style="font-size: 1.2em;">{{otp}}</strong></p>
      <p>This One-Time Code is valid for the next <strong>{{expiry_time}}</strong> minutes. Please do not share this One-Time Code with anyone.</p>
      <p>If you did not request this, please contact our support team immediately.</p>
      <p style="margin-top: 20px;">Best regards,</p>
      <p style="font-weight: bold;">The Happy House Team</p>
    </div>

    <!-- Right Logo -->
    <div style="flex-shrink: 0; text-align: center; min-width: 200px;">
     <img src='cid:$cid' alt='The Happy House Logo' alt="The Happy House Logo" style="max-width: 200px; height: auto;">

    </div>
  </div>

  <!-- Responsive Styles -->
  <style>
    @media (max-width: 600px) {
      div[style*="display: flex"] {
        flex-direction: column;
        text-align: center;
      }

      /* Make the logo appear first */
      div[style*="min-width: 200px"] {
        
        margin-bottom: 20px;
      }

      div[style*="flex: 1"] {
        padding-right: 0;
      }
      p
      {
        text-align:left;
      }
    }
  </style>
</body>
</html>