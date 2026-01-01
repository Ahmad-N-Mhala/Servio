# POS Printer - Quick Reference

## 🖨️ How It Works

### User Flow
1. **Select Order** → Click on an active order in the POS page
2. **Review Bill** → Check items, totals, discounts, charges
3. **Print Option** → "Print Bill (POS Printer)" checkbox (✅ checked by default)
4. **Payment** → Select payment method (Cash/Card/Online)
5. **Settle** → Click "Settle" button
6. **Auto-Print** → If checkbox is checked, receipt prints automatically

---

## ✅ Supported Printers

### Thermal Printers (Recommended)
- **80mm**: Epson TM-T88, Star TSP100, Bixolon SRP-350
- **58mm**: Epson TM-T20, Star SM-S230i

### Standard Printers (Fallback)
- Any A4/Letter printer with driver installed

---

## 🧪 Testing Without Printer

### Quick Test Method
1. Go to POS page: `http://127.0.0.1:8000/en/pos`
2. Select any active order
3. Ensure checkbox is checked ✅
4. Click "Settle"
5. In print dialog → Select **"Save as PDF"**
6. Review the PDF receipt

**Expected Result**: Clean, formatted receipt with all order details

---

## 🔧 Setup for Production

### One-Time Setup
```bash
1. Install printer driver (from manufacturer)
2. Connect printer (USB or Network)
3. Set paper size to 80mm (or 58mm)
4. Set as default printer (optional)
5. Test print from browser
```

### Daily Use
- No setup needed!
- Just check/uncheck the checkbox as needed
- Print dialog appears automatically

---

## 🚨 Quick Troubleshooting

| Issue | Solution |
|-------|----------|
| No print dialog | Check browser pop-up blocker |
| Logo missing | Normal - auto-hides if not available |
| Wrong formatting | Verify printer paper size setting |
| Text cut off | Set printer margins to 0 |
| Multiple prints | Browser/driver issue - check queue |

---

## 📋 Receipt Contains

✅ Restaurant logo & info  
✅ Order number & date/time  
✅ Table & customer name  
✅ Order type & payment method  
✅ All items with quantities  
✅ Subtotal, tax, discounts  
✅ Grand total (bold)  
✅ Thank you message  

---

## 🎯 Key Features

- ✅ **Default ON** - Checkbox checked automatically
- ✅ **User Control** - Can uncheck to skip printing
- ✅ **Multi-Printer** - Works with 80mm, 58mm, A4
- ✅ **Error Handling** - Alerts if printing fails
- ✅ **Security** - XSS protection built-in
- ✅ **No Hardware Needed** - Test with PDF

---

## 📞 Support

**Full Documentation**: See `.agent/POS_PRINTER_TESTING_GUIDE.md`  
**Implementation Details**: See `.agent/POS_PRINTER_IMPLEMENTATION_SUMMARY.md`  
**Console Logs**: Check browser console for detailed errors

---

## ⚡ Pro Tips

1. **Testing**: Always use "Save as PDF" first
2. **Production**: Set thermal printer as default
3. **Speed**: Keep checkbox checked for faster workflow
4. **Backup**: Standard printer works if thermal fails
5. **Training**: Show staff the checkbox location

---

**Status**: ✅ Ready for Testing  
**Last Updated**: January 1, 2026
