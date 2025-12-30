<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .footer {
            margin-top: 30px;
            font-size: 0.8em;
            color: #777;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        {!! nl2br(e($customContent)) !!}

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>

</html>