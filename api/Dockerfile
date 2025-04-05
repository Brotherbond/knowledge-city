FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy composer.json
COPY composer.json /var/www/html/

# Set entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]