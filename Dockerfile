FROM php:8.2.12-apache

COPY ./public_html/ /var/www/html/
COPY ./my-htpasswd /etc/apache2/htpasswd
COPY ./my-apache2.conf /etc/apache2/apache2.conf
