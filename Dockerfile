FROM php:8.2-apache

# copy toàn bộ code vào web server
COPY . /var/www/html/

# bật rewrite (nếu cần)
RUN a2enmod rewrite

EXPOSE 80
