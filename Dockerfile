FROM php:8.3-apache

RUN docker-php-ext-install mysqli
COPY . /var/www/html/
# URL paths use /ClickClean so the same source works in XAMPP's htdocs folder.
# This alias keeps those links working when Docker serves the app from its web root.
RUN ln -s /var/www/html /var/www/html/ClickClean \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
