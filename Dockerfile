# Usamos una imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalamos las extensiones necesarias para conectar con MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Activamos el módulo "rewrite" de Apache (útil para URLs amigables después)
RUN a2enmod rewrite