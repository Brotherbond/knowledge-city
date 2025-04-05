#!/bin/bash
set -e

# Check if vendor directory exists
if [ ! -d "/var/www/html/vendor" ]; then
    echo "Vendor directory not found. Running composer dump-autoload..."

    # Run composer dump-autoload
    cd /var/www/html
    composer dump-autoload

    echo "Composer dump-autoload completed successfully."
fi

# Start Apache in foreground
apache2-foreground
