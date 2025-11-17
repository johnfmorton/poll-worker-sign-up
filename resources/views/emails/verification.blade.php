<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
        }
        p {
            margin-bottom: 15px;
            color: #555;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #3490dc;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: 600;
        }
        .button:hover {
            background-color: #2779bd;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #888;
        }
        .link-text {
            word-break: break-all;
            color: #3490dc;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verify Your Email Address</h1>
        
        <p>Hello {{ $name }},</p>
        
        <p>Thank you for registering to become a poll worker in Warren, CT. To complete your registration, please verify your email address by clicking the button below:</p>
        
        <a href="{{ $verification_url }}" class="button">Verify Email Address</a>
        
        <p>If the button above doesn't work, you can copy and paste the following link into your browser:</p>
        
        <p class="link-text">{{ $verification_url }}</p>
        
        <p>This verification link will expire in 48 hours.</p>
        
        <p>If you did not register for poll worker service, please disregard this email.</p>
        
        <div class="footer">
            <p>This is an automated message from the Warren, CT Poll Worker Registration System. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
