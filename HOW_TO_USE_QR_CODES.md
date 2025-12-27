# 📱 How Restaurant Users Can View & Use QR Codes

## ✅ QR Code Feature Now Available!

Restaurant users can now view, download, and manage QR codes for all tables directly from the Tables Management page.

---

## 🎯 How to Access QR Codes

### Step 1: Go to Tables Management

1. Login to RestoFy
2. Navigate to **Tables** from the sidebar menu
3. You'll see all your tables displayed as cards

### Step 2: View QR Code

1. **Hover over any table card**
2. You'll see action buttons appear in the top-right corner
3. Click the **purple QR code icon** (first button)
4. A modal will open showing:
   - The QR code (large, scannable)
   - Table information
   - QR code URL
   - Instructions on how to use it

### Step 3: Download QR Code

1. In the QR code modal, click **"Download PNG"** button (green)
2. A high-quality PNG image (300x300px) will download
3. Print this image and place it on the table
4. Customers can now scan it to order!

### Step 4: Regenerate QR Code (Optional)

1. If you need a new QR code (security, lost code, etc.)
2. Click the **"Regenerate"** button (orange)
3. Confirm the action
4. A new QR code will be generated
5. **Important**: The old QR code will no longer work

---

## 🖼️ Visual Guide

### Table Card with QR Button:

```
┌─────────────────────────┐
│  [QR] [Edit] [Delete]  │ ← Hover to see buttons
│                         │
│       ┌─────┐           │
│       │ T-1 │           │ ← Table name
│       └─────┘           │
│     Main Hall           │ ← Location
│      4 Seats            │ ← Capacity
│                         │
│    [Available]          │ ← Status
└─────────────────────────┘
```

### QR Code Modal:

```
┌────────────────────────────────┐
│  QR Code - Table 1             │
│                                │
│  ┌──────────────────┐          │
│  │                  │          │
│  │   [QR CODE]      │          │ ← Scannable QR
│  │                  │          │
│  └──────────────────┘          │
│                                │
│  Table: Table 1                │
│  Location: Main Hall           │
│  Capacity: 4 seats             │
│  URL: http://...               │
│                                │
│  How to use:                   │
│  1. Download the QR code       │
│  2. Print it                   │
│  3. Place on table             │
│  4. Customers scan to order    │
│                                │
│  [Download PNG] [Regenerate]   │
│  [Close]                       │
└────────────────────────────────┘
```

---

## 📋 What Each Button Does

### Purple QR Icon Button:
- **Function**: View QR code
- **Permission**: view_tables
- **Action**: Opens modal with QR code preview

### Green Download Button:
- **Function**: Download QR code as PNG
- **Permission**: view_tables
- **Action**: Downloads 300x300px PNG file

### Orange Regenerate Button:
- **Function**: Create new QR code
- **Permission**: edit_table
- **Action**: Generates new token, old code stops working

---

## 🎨 Printing Tips

### For Best Results:

1. **Download the QR code** as PNG
2. **Print on white paper** or cardstock
3. **Size**: Keep it at least 3x3 inches (7.5x7.5 cm)
4. **Quality**: Use high-quality printer settings
5. **Placement**: Put it where customers can easily see and scan
6. **Protection**: Consider laminating for durability

### Recommended Sizes:

- **Small tables**: 3x3 inches (7.5x7.5 cm)
- **Medium tables**: 4x4 inches (10x10 cm)
- **Large tables**: 5x5 inches (12.5x12.5 cm)

---

## 📱 Customer Experience

### When a customer scans the QR code:

1. **Opens menu** on their phone
2. **Sees restaurant name** and table number
3. **Browses menu** by categories
4. **Adds items** to cart
5. **Places order** with one tap
6. **Gets confirmation** with order number
7. **Order appears** in your kitchen/POS automatically

---

## 🔐 Security Features

### QR Code Security:

✅ **Unique tokens** - Each table has a unique 32-character code
✅ **Regenerate anytime** - Create new codes if compromised
✅ **Table-specific** - Each QR only works for its table
✅ **No sensitive data** - QR contains only a URL, no prices or data

---

## ❓ Frequently Asked Questions

### Q: Can I print multiple copies of the same QR code?
**A:** Yes! You can print as many copies as you need for the same table.

### Q: What happens if I regenerate a QR code?
**A:** The old QR code stops working immediately. You'll need to print and replace it.

### Q: Can customers order from any table's QR code?
**A:** No, each QR code is linked to a specific table. Orders will be associated with that table.

### Q: What if a customer scans the wrong table's QR code?
**A:** The order will be linked to the table whose QR code they scanned. Staff should verify table numbers.

### Q: Do I need internet to scan QR codes?
**A:** Yes, customers need internet to access the menu and place orders.

### Q: Can I customize the QR code design?
**A:** Currently, QR codes are generated in standard format. Custom designs may be added in future updates.

---

## 🛠️ Troubleshooting

### Issue: QR code button not showing
**Solution**: Make sure you have `view_tables` permission

### Issue: Can't download QR code
**Solution**: Check your browser's download settings and permissions

### Issue: QR code doesn't scan
**Solution**: 
- Ensure good lighting
- Print at adequate size (minimum 3x3 inches)
- Use high-quality printer
- Check if QR code was regenerated (old codes won't work)

### Issue: Orders going to wrong table
**Solution**: Verify each table has the correct QR code placed on it

---

## ✅ Quick Checklist

Before placing QR codes on tables:

- [ ] All tables created in system
- [ ] QR codes downloaded for each table
- [ ] QR codes printed clearly
- [ ] Correct size (minimum 3x3 inches)
- [ ] Laminated or protected
- [ ] Placed on correct tables
- [ ] Tested by scanning
- [ ] Staff trained on QR ordering

---

## 🎉 Benefits

### For Your Restaurant:

✅ **Faster service** - No waiting for staff to take orders
✅ **Fewer errors** - Customers enter their own orders
✅ **Higher efficiency** - Staff can focus on service
✅ **Better experience** - Modern, contactless ordering
✅ **Increased sales** - Customers can browse full menu
✅ **Order tracking** - All orders linked to tables

### For Customers:

✅ **Convenience** - Order anytime without waiting
✅ **Full menu** - See all items with images
✅ **Special requests** - Add notes to items
✅ **Order tracking** - Know order status
✅ **Contactless** - Safe and hygienic

---

## 📞 Need Help?

If you have questions or issues:

1. Check this guide first
2. Contact your system administrator
3. Refer to the technical documentation in `.agent/QR_ORDERING_IMPLEMENTATION.md`

---

**Ready to use QR codes!** 🚀

Just go to **Tables** → **Hover on table** → **Click QR icon** → **Download** → **Print** → **Place on table**!
