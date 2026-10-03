FROM php:8.3-fpm-alpine

# Instalación de la extensión PDO MySQL/MariaDB necesaria para la API
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

# Copia los archivos fuente de la API
COPY . .

EXPOSE 8113

# Servidor embebido de PHP escuchando en todas las interfaces para la API
CMD ["php", "-S", "0.0.0.0:8113", "-t", "/var/www/html"]