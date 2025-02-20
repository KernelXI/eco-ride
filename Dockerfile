# Utiliser l'image PHP officielle avec Apache
FROM php:8.0-apache

# Installer les extensions nécessaires (ajoutez celles dont vous avez besoin)
RUN docker-php-ext-install pdo pdo_mysql

# Copier le code de l'application dans le conteneur
COPY . /var/www/html/

# Exposer le port 80 pour le trafic HTTP
EXPOSE 80

# Configurer le point d'entrée (facultatif, mais peut être utile)
CMD ["apache2-foreground"]
