#!/bin/bash

# Email Configuration Update Script for Servio
# This script helps update the .env file with email settings

echo "================================================"
echo "Servio Email Configuration Setup"
echo "================================================"
echo ""

ENV_FILE=".env"

if [ ! -f "$ENV_FILE" ]; then
    echo "❌ Error: .env file not found!"
    echo "Please make sure you're running this script from the project root."
    exit 1
fi

echo "📧 Updating email configuration in .env file..."
echo ""

# Function to update or add env variable
update_env() {
    local key=$1
    local value=$2
    
    if grep -q "^${key}=" "$ENV_FILE"; then
        # Update existing
        if [[ "$OSTYPE" == "darwin"* ]]; then
            # macOS
            sed -i '' "s|^${key}=.*|${key}=${value}|" "$ENV_FILE"
        else
            # Linux
            sed -i "s|^${key}=.*|${key}=${value}|" "$ENV_FILE"
        fi
        echo "✅ Updated ${key}"
    else
        # Add new
        echo "${key}=${value}" >> "$ENV_FILE"
        echo "✅ Added ${key}"
    fi
}

# Update email settings
update_env "MAIL_MAILER" "smtp"
update_env "MAIL_HOST" "smtp.gmail.com"
update_env "MAIL_PORT" "587"
update_env "MAIL_USERNAME" "restaurfydaoudmhala@gmail.com"
update_env "MAIL_PASSWORD" '"ADMhala@@7@@"'
update_env "MAIL_ENCRYPTION" "tls"
update_env "MAIL_FROM_ADDRESS" "restaurfydaoudmhala@gmail.com"
update_env "MAIL_FROM_NAME" '"Servio"'

echo ""
echo "================================================"
echo "⚠️  IMPORTANT: Gmail App Password Required"
echo "================================================"
echo ""
echo "Gmail no longer accepts regular passwords for SMTP."
echo "You need to generate an App Password:"
echo ""
echo "1. Go to: https://myaccount.google.com/"
echo "2. Navigate to Security"
echo "3. Enable 2-Step Verification (if not enabled)"
echo "4. Under 'Signing in to Google', click 'App passwords'"
echo "5. Select 'Mail' and 'Other (Custom name)'"
echo "6. Enter 'Servio' as the name"
echo "7. Copy the 16-character password"
echo "8. Replace the MAIL_PASSWORD value in .env with it"
echo ""
echo "================================================"
echo "✅ Email configuration updated!"
echo "================================================"
echo ""
echo "Next steps:"
echo "1. Generate Gmail App Password (see above)"
echo "2. Update MAIL_PASSWORD in .env with the App Password"
echo "3. Run: php artisan config:clear"
echo "4. Test by creating a new staff member"
echo ""
