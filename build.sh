#!/bin/bash
set -e

echo "Installing Composer..."
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "Build complete."
