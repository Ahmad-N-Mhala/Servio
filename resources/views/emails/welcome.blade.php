<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Welcome to RestoFy</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            width: 100% !important;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333333;
            font-size: 24px;
            margin: 0;
        }

        .content {
            color: #555555;
            font-size: 16px;
            line-height: 1.6;
        }

        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        .button {
            display: inline-block;
            background-color: #ffffff;
            color: #4F46E5;
            font-size: 16px;
            font-weight: bold;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            border: 2px solid #4F46E5;
            transition: all 0.3s ease;
        }

        .button:hover {
            background-color: #4F46E5;
            color: #ffffff;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #999999;
        }

        .footer p {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to RestoFy!</h1>
        </div>
        <div class="content">
            <p>Dear {{ $user->name }},</p>
            <p>Welcome to the system! You have been successfully added to the <strong>{{ $restaurantName }}</strong>
                team.</p>
            <p>We are excited to have you on board. To get started, please set your password by clicking the button
                below:</p>

            <div class="button-container">
                <a href="{{ $resetUrl }}" class="button">Set Your Password</a>
            </div>

            <p>This link will expire in 60 minutes.</p>
            <p>If you did not expect this invitation, please ignore this email.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} RestoFy. All rights reserved.</p>
        </div>
    </div>
</body>

</html>