# Use the official PHP image with Apache
FROM php:8.2-apache

# Copy all project files into the web server directory
COPY . /var/www/html/

# Expose port 80 for web traffic
EXPOSE 80

# Enable Apache rewrite module (important for some PHP apps)
RUN a2enmod rewrite

# Start Apache in the foreground
CMD ["apache2-foreground"]
