# Automated Translation Script - Usage Guide

## ✅ What This Script Does

The `translate-vue-files.js` script automatically scans all Vue files in your `resources/js/Pages` directory and replaces common hardcoded English text with translation keys.

## 🚀 How to Use

### Step 1: Backup Your Code
```bash
git add .
git commit -m "Before automated translation"
```

### Step 2: Run the Script
```bash
node translate-vue-files.js
```

### Step 3: Review Changes
```bash
git diff
```

### Step 4: Test the Application
1. Start your servers (already running)
2. Visit pages in English: `http://127.0.0.1:8000/en/dashboard`
3. Switch to Arabic: `http://127.0.0.1:8000/ar/dashboard`
4. Verify all text is translated correctly

### Step 5: Commit Changes
```bash
git add .
git commit -m "Automated translation implementation"
```

## 📋 What Gets Replaced

The script replaces:

### Common Buttons
- Save, Cancel, Delete, Edit, Create, Update
- Close, Submit, Add, Remove, View
- Export, Import, Search, Filter, Reset
- Back, Next, Confirm

### Common Labels
- Name, Description, Price, Status, Actions
- Date, Time, Total, Quantity

### Common Status
- Active, Inactive, Loading...
- No data available

### Chart Titles
- Revenue Trend, Order Status, Payment Methods
- Top Menu Items, Peak Hours, Waste Trend
- And more...

### Page-Specific Text
- Kitchen: Pending, Processing, Dine In, Takeaway
- Dashboard: Net Profit, Low Stock, Avg Dining Time
- And more...

## ⚠️ Important Notes

### What the Script Does NOT Do

1. **Complex Sentences** - Only replaces simple, common words/phrases
2. **Dynamic Content** - Doesn't touch JavaScript variables or computed properties
3. **Context-Specific Text** - May need manual review for context

### Manual Review Required

After running the script, you MUST:

1. **Check for over-replacements** - Sometimes "Save" in a sentence shouldn't be replaced
2. **Add missing keys** - If you see text that wasn't replaced, add it manually
3. **Test thoroughly** - Especially forms, modals, and error messages

## 🔧 Customizing the Script

To add more replacements, edit `translate-vue-files.js`:

```javascript
const replacements = [
    // Add your custom patterns here
    [/>\s*Your Text\s*</g, ">{{ $t('section.key') }}<", 'Description'],
];
```

## 📊 Expected Results

The script should:
- Process 60+ Vue files
- Make 500-1000+ replacements
- Complete in under 1 minute

## 🐛 Troubleshooting

### Script Errors
```bash
# If you get "node: command not found"
# Node.js is not installed - but you have npm running, so this shouldn't happen

# If you get permission errors
chmod +x translate-vue-files.js
```

### Incorrect Replacements

If the script replaces something incorrectly:
1. Use `git diff` to find the change
2. Manually revert that specific change
3. Update the script to skip that pattern

### Missing Translations

If you see English text after running the script:
1. Check if the translation key exists in `/resources/js/locales/en/index.ts`
2. If not, add it to both English and Arabic files
3. Update the Vue file manually

## 📝 Post-Script Checklist

- [ ] Run the script
- [ ] Review git diff
- [ ] Test in English
- [ ] Test in Arabic
- [ ] Fix any issues
- [ ] Commit changes
- [ ] Deploy to production

## 🎯 Estimated Time Savings

- **Without script**: 20-30 hours of manual work
- **With script**: 3-5 hours (script + review + fixes)
- **Time saved**: ~25 hours

## 💡 Pro Tips

1. **Run in batches** - Process one directory at a time if you want more control
2. **Test frequently** - Don't wait until all files are done to test
3. **Use git** - Commit after each successful batch
4. **Keep backups** - Always have a backup before running automated scripts

## 🆘 Need Help?

If you encounter issues:
1. Check the console output for specific errors
2. Review the `TRANSLATION_GUIDE.md` for manual translation patterns
3. Use `/admin/localization` to manage translation keys
4. Check completed pages (Kitchen, Reports) for reference

---

**Ready to run? Execute: `node translate-vue-files.js`**
