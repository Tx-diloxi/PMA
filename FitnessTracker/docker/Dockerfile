FROM php:8.2-apache

RUN apt-get update \
  && apt-get install -y libzip-dev libonig-dev zlib1g-dev \
  && docker-php-ext-install pdo pdo_mysql mysqli \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*

# Optional custom composer etc.
