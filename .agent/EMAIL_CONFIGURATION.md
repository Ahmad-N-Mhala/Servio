# Email Configuration for Servio

## Environment Variables

Add the following lines to your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=Serviodaoudmhala@gmail.com
MAIL_PASSWORD="ADMhala@@7@@"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=Serviodaoudmhala@gmail.com
MAIL_FROM_NAME="Servio"
```

## Important Notes

### Gmail App Password Required

⚠️ **IMPORTANT**: Gmail no longer accepts regular passwords for SMTP authentication. You need to use an **App Password** instead.

### Steps to Generate Gmail App Password:

1. Go to your Google Account: https://myaccount.google.com/
2. Navigate to **Security**
3. Enable **2-Step Verification** (if not already enabled)
4. Once 2-Step Verification is enabled, go back to Security
5. Under "Signing in to Google", click on **App passwords**
6. Select **Mail** as the app and **Other (Custom name)** as the device
7. Enter "Servio" as the custom name
8. Click **Generate**
9. Copy the 16-character app password (it will look like: `xxxx xxxx xxxx xxxx`)
10. Replace `ADMhala@@7@@` in your `.env` file with this app password

### Example with App Password:

```env
MAIL_PASSWORD="abcd efgh ijkl mnop"
```

## Testing Email Configuration

After updating your `.env` file, you can test the email configuration by:

1. Clearing the config cache:
   ```bash
   php artisan config:clear
   ```

2. Testing email sending through the application (e.g., creating a new user or requesting a password reset)

## Troubleshooting

If emails are not sending:

1. **Check Gmail Security Settings**: Make sure "Less secure app access" is OFF and you're using an App Password
2. **Verify 2-Step Verification**: Must be enabled to generate App Passwords
3. **Check Firewall**: Ensure port 587 is not blocked
4. **Clear Config Cache**: Run `php artisan config:clear` after any changes
5. **Check Logs**: Review `storage/logs/laravel.log` for error messages

## Alternative: Using Gmail with OAuth2

For production environments, consider using OAuth2 authentication instead of App Passwords for better security.
