FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

COPY . .

EXPOSE 8112

# Servidor embebido apuntando únicamente al directorio raíz
CMD ["php", "-S", "0.0.0.0:8112", "-t", "/var/www/html"]