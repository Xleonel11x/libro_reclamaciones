#!/bin/bash
set -e

# Install Composer if not already available
if ! command -v composer &> /dev/null; then
    echo "Installing Composer..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm composer-setup.php
fi

# Install dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "Build complete."
