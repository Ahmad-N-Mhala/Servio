# Quick Email Setup Guide

## ✅ Configuration Complete!

Your Servio email system has been configured with:
- **Email**: Serviodaoudmhala@gmail.com
- **SMTP**: Gmail (smtp.gmail.com:587)

---

## ⚠️ IMPORTANT: One More Step Required!

**Gmail requires an App Password instead of your regular password.**

### Quick Steps:

1. **Visit**: https://myaccount.google.com/apppasswords
2. **Enable 2-Step Verification** (if not already enabled)
3. **Generate App Password**:
   - App: Mail
   - Device: Other (Servio)
4. **Copy the 16-character password**
5. **Update `.env` file**:
   - Find: `MAIL_PASSWORD="ADMhala@@7@@"`
   - Replace with: `MAIL_PASSWORD="your-app-password-here"`
6. **Clear cache**: `php artisan config:clear`

---

## 🧪 Test Your Configuration

Run this command to test if emails are working:

```bash
php artisan email:test
```

Or send to a specific email:

```bash
php artisan email:test your-email@example.com
```

---

## 📧 Where Emails Are Sent

1. **New Staff Member**: Welcome email with password reset link
2. **Password Reset**: Reset link when users forget password

---

## 🔍 Troubleshooting

**Emails not sending?**

1. Check you're using App Password (not regular password)
2. Verify 2-Step Verification is enabled
3. Run: `php artisan config:clear`
4. Check logs: `tail -f storage/logs/laravel.log`

---

## 📚 Full Documentation

See `.agent/EMAIL_SETUP_SUMMARY.md` for complete details.

---

**Status**: ⏳ Waiting for Gmail App Password
**Next**: Generate App Password → Update .env → Test
