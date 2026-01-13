FROM php:8.2-apache

# Enable Apache rewrite (very important for PHP apps)
RUN a2enmod rewrite

# Copy project files into Apache root
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
