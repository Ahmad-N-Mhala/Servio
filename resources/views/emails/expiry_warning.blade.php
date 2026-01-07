<!DOCTYPE html>
<html>

<head>
    <title>Expiry Warning</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="background-color: #f8f9fa; padding: 20px; text-align: center;">
        <h2 style="color: #d9534f;">⚠️ Batch Expiry Warning / تحذير انتهاء الصلاحية</h2>
    </div>

    <div style="padding: 20px;">
        <p><strong>English:</strong></p>
        <p>The following inventory batch is approaching its expiration date.</p>

        <p><strong>Arabic:</strong></p>
        <p>الدفعة المخزنية التالية تقترب من تاريخ انتهاء صلاحيتها.</p>

        <hr style="border: 1px solid #eee; margin: 20px 0;">

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px; font-weight: bold;">Ingredient / المكون:</td>
                <td style="padding: 8px;">
                    {{ $batch->ingredient->name['en'] ?? '' }}
                    <br>
                    <span style="color: #666;">{{ $batch->ingredient->name['ar'] ?? '' }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; font-weight: bold;">Batch Number / رقم الدفعة:</td>
                <td style="padding: 8px;">{{ $batch->batch_number }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; font-weight: bold;">Expiration Date / تاريخ الانتهاء:</td>
                <td style="padding: 8px;">{{ $batch->expiration_date->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; font-weight: bold;">Days Remaining / الأيام المتبقية:</td>
                <td style="padding: 8px; color: #d9534f; font-weight: bold;">{{ $daysRemaining }} Days/أيام</td>
            </tr>
            <tr>
                <td style="padding: 8px; font-weight: bold;">Remaining Quantity / الكمية المتبقية:</td>
                <td style="padding: 8px;">{{ $batch->quantity_remaining }} {{ $batch->ingredient->unit }}</td>
            </tr>
        </table>

        <div style="margin-top: 30px; text-align: center;">
            <a href="{{ url('/inventory') }}"
                style="background-color: #0275d8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                Manage Inventory / إدارة المخزون
            </a>
        </div>
    </div>
</body>

</html>