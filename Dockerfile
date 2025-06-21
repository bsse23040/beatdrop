# Use official PHP image with Apache
FROM php:8.2-apache

# Enable required extensions
RUN docker-php-ext-install pdo pdo_pgsql

# Copy your app code
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]