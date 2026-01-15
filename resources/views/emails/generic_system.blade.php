<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $customSubject }}</title>
</head>

<body
    style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f4f7f6; margin: 0; padding: 0;">
    <div
        style="max-width: 600px; margin: 20px auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2
            style="color: #4F46E5; margin-top: 0; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 20px;">
            {{ $customSubject }}
        </h2>

        <div style="font-size: 16px; color: #4b5563;">
            {!! $customContent !!}
        </div>

        <div
            style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #f0f0f0; font-size: 12px; color: #9ca3af; text-align: center;">
            Sent by Servio System
        </div>
    </div>
</body>

</html>