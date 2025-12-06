#!/bin/bash

# Exit on error
set -e

echo "🚀 Starting Environment Setup for RestaurFy..."

# 1. Check/Install Homebrew
if ! command -v brew &> /dev/null; then
    echo "📦 Homebrew not found. Installing..."
    /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
    
    # Add to PATH for Apple Silicon or Intel
    if [[ -f /opt/homebrew/bin/brew ]]; then
        eval "$(/opt/homebrew/bin/brew shellenv)"
    elif [[ -f /usr/local/bin/brew ]]; then
        eval "$(/usr/local/bin/brew shellenv)"
    fi
else
    echo "✅ Homebrew is already installed."
fi

# 2. Install Dependencies
echo "📦 Installing dependencies..."
brew update
brew install php composer node postgresql@14 redis

# 3. Start Services
echo "🚀 Starting services..."
brew services start postgresql@14
brew services start redis

# 4. Link PHP if needed
if ! command -v php &> /dev/null; then
    brew link php --force
fi

echo "✅ Environment setup complete!"
echo "Please restart your terminal or run: source ~/.zshrc (or ~/.bash_profile)"
