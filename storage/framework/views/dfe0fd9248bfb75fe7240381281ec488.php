<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bill #<?php echo e($order->order_number); ?></title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 30px;
        }

        .restaurant-name {
            font-size: 32px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .restaurant-info {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

        .bill-details {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }

        .col-left,
        .col-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .col-right {
            text-align: right;
        }

        .label {
            font-weight: 700;
            color: #888;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .value {
            margin-bottom: 15px;
            font-size: 15px;
            color: #222;
        }

        .value strong {
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th {
            text-align: left;
            background-color: #f8f9fa;
            color: #555;
            font-weight: 700;
            padding: 15px 12px;
            border-bottom: 2px solid #eee;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 15px 12px;
            border-bottom: 1px solid #f5f5f5;
            color: #333;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-section {
            width: 100%;
            display: table;
            margin-top: 20px;
        }

        .totals-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }

        .totals-right {
            display: table-cell;
            width: 50%;
            text-align: right;
        }

        .total-row {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .total-row span {
            display: inline-block;
            min-width: 100px;
        }

        .grand-total {
            font-size: 22px;
            font-weight: 800;
            color: #000;
            border-top: 2px solid #000;
            padding-top: 15px;
            margin-top: 15px;
        }

        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #f0f0f0;
            padding-top: 20px;
        }

        .status-badge {
            background: #eee;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .notes-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="restaurant-name"><?php echo e($order->restaurant->name ?? $tenant->name ?? 'RestaurFy'); ?></div>
            <div class="restaurant-info">
                <?php if($order->restaurant->address): ?> <?php echo e($order->restaurant->address); ?> <?php endif; ?>
                <?php if($order->restaurant->city): ?> , <?php echo e($order->restaurant->city); ?> <?php endif; ?>
                <?php if($order->restaurant->country): ?> , <?php echo e($order->restaurant->country); ?> <?php endif; ?>
                <?php if($order->restaurant->phone): ?> <br> Tel: <?php echo e($order->restaurant->phone); ?> <?php endif; ?>
                <?php if($order->restaurant->email): ?> <br> <?php echo e($order->restaurant->email); ?> <?php endif; ?>
            </div>
        </div>

        <div class="bill-details">
            <div class="col-left">
                <div class="label">Bill To</div>
                <div class="value">
                    <?php if($order->customer): ?>
                        <strong><?php echo e($order->customer->name); ?></strong><br>
                        <?php echo e($order->customer->phone); ?>

                    <?php else: ?>
                        Guest
                    <?php endif; ?>
                </div>

                <?php if($order->table): ?>
                    <div class="label">Table</div>
                    <div class="value">#<?php echo e($order->table->table_number); ?> <span
                            style="color:#888; font-size:12px">(<?php echo e($order->table->location ?? 'Main'); ?>)</span></div>
                <?php endif; ?>
            </div>
            <div class="col-right">
                <div class="label">Order Details</div>
                <div class="value">
                    Order #: <strong><?php echo e($order->order_number); ?></strong><br>
                    Date: <?php echo e($order->created_at->format('M d, Y h:i A')); ?><br>
                    Status: <?php echo e(ucfirst($order->status)); ?>

                    <?php if(isset($order->waiter)): ?>
                        <br>Waiter: <?php echo e($order->waiter->name); ?>

                    <?php endif; ?>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="50%">Item</th>
                    <th width="15%" class="text-center">Qty</th>
                    <th width="15%" class="text-right">Price</th>
                    <th width="20%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <strong style="font-weight:600; color:#333;"><?php echo e($item->menuItem->name ?? 'Item'); ?></strong>
                            <?php if(isset($item->notes)): ?>
                                <br><small
                                    style="color: #999; font-size: 11px; margin-top:2px; display:block;"><?php echo e($item->notes); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?php echo e($item->quantity); ?></td>
                        <td class="text-right"><?php echo e(number_format($item->unit_price, 2)); ?></td>
                        <td class="text-right"><?php echo e(number_format($item->total_price, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <div class="totals-section">
            <div class="totals-left">
                <?php if($order->notes): ?>
                    <div class="notes-box">
                        <strong>Note:</strong><br>
                        <?php echo e($order->notes); ?>

                    </div>
                <?php endif; ?>

                <?php if($order->points_earned > 0): ?>
                    <div style="margin-top: 15px; color: #4f46e5; font-size: 13px;">
                        🎉 You earned <strong><?php echo e($order->points_earned); ?></strong> loyalty points!
                    </div>
                <?php endif; ?>
            </div>
            <div class="totals-right">
                <div class="total-row">
                    <span style="color: #666;">Subtotal</span> <?php echo e(number_format($order->subtotal, 2)); ?>

                </div>
                <?php if($order->tax > 0): ?>
                    <div class="total-row">
                        <span style="color: #666;">Tax</span> <?php echo e(number_format($order->tax, 2)); ?>

                    </div>
                <?php endif; ?>
                <?php if($order->discount_amount > 0): ?>
                    <div class="total-row" style="color: #e53e3e;">
                        <span>Discount</span> -<?php echo e(number_format($order->discount_amount, 2)); ?>

                    </div>
                <?php endif; ?>
                <div class="grand-total">
                    <span
                        style="font-size: 14px; font-weight: normal; color: #666; min-width: auto; margin-right: 15px;">Total</span>
                    <?php echo e($order->currency ?? 'AED'); ?> <?php echo e(number_format($order->total, 2)); ?>

                </div>
            </div>
        </div>

        <div class="footer">
            Thank you for dining with
            <strong><?php echo e($order->restaurant->name ?? $tenant->name ?? 'RestaurFy'); ?></strong>!<br>
            <span style="opacity: 0.6; font-size: 10px;">Powered by RestaurFy</span>
        </div>
    </div>
</body>

</html><?php /**PATH /Users/ahmadmhala/Downloads/RestoFy-main/resources/views/bills/order.blade.php ENDPATH**/ ?>