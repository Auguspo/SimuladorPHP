FROM php:8.2-apache
RUN a2enmod rewrite && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
RUN docker-php-ext-install pdo pdo_mysql
