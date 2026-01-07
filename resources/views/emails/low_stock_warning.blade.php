<!DOCTYPE html>
<html>

<head>
    <title>Low Stock Warning</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="background-color: #f8f9fa; padding: 20px; text-align: center;">
        <h2 style="color: #f0ad4e;">⚠️ Low Stock Warning / تحذير انخفاض المخزون</h2>
    </div>

    <div style="padding: 20px;">
        <p><strong>English:</strong></p>
        <p>The following inventory item has reached its reorder level.</p>

        <p><strong>Arabic:</strong></p>
        <p>المكون التالي وصل إلى حد إعادة الطلب.</p>

        <hr style="border: 1px solid #eee; margin: 20px 0;">

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px; font-weight: bold;">Ingredient / المكون:</td>
                <td style="padding: 8px;">
                    {{ $ingredient->name['en'] ?? '' }}
                    <br>
                    <span style="color: #666;">{{ $ingredient->name['ar'] ?? '' }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; font-weight: bold;">Current Stock / المخزون الحالي:</td>
                <td style="padding: 8px; color: #d9534f; font-weight: bold;">{{ $ingredient->current_stock }}
                    {{ $ingredient->unit }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; font-weight: bold;">Reorder Level / حد إعادة الطلب:</td>
                <td style="padding: 8px;">{{ $ingredient->reorder_level }} {{ $ingredient->unit }}</td>
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