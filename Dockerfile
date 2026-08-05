# SahaNet PRO Dockerfile
FROM php:8.2-apache

# Install required PHP extensions (pdo, pdo_mysql)
RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

# Set Apache working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Set permissions for web server
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
