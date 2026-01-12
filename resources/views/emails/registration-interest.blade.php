<!DOCTYPE html>
<html dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .content {
            padding: 30px;
        }

        .plan-badge {
            display: inline-block;
            background-color: #ecfdf5;
            color: #047857;
            padding: 8px 16px;
            border-radius: 9999px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .detail-row {
            margin-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 8px;
        }

        .detail-label {
            font-size: 13px;
            color: #6b7280;
            display: block;
            margin-bottom: 4px;
        }

        .detail-value {
            font-size: 16px;
            color: #111827;
            font-weight: 500;
        }

        .message-box {
            background-color: #f9fafb;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin-top: 25px;
            font-style: italic;
        }

        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>New Interest Received</h1>
        </div>
        <div class="content">
            <div class="plan-badge">
                Plan: {{ $data['plan_name'] }}
            </div>

            <div class="detail-row">
                <span class="detail-label">Full Name</span>
                <span class="detail-value">{{ $data['name'] }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Email Address</span>
                <span class="detail-value">{{ $data['email'] }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Phone Number</span>
                <span class="detail-value">{{ $data['phone'] }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Restaurant Name</span>
                <span class="detail-value">{{ $data['restaurant_name'] }}</span>
            </div>

            @if(!empty($data['message']))
                <div class="message-box">
                    "{{ $data['message'] }}"
                </div>
            @endif
        </div>
        <div class="footer">
            Sent from Servio Landing Page &bull; {{ date('Y-m-d H:i') }}
        </div>
    </div>
</body>

</html>