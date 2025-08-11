# Imagem oficial do PHP com Apache
FROM php:8.3-apache

# Instala extensões necessárias
RUN docker-php-ext-install pdo pdo_mysql

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia o projeto
COPY . /var/www/html

# Define a pasta public como root do Apache
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Instala dependências do Composer (se houver composer.json)
WORKDIR /var/www/html
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi

# Exponha a porta 8080
EXPOSE 8080

# Altera a porta padrão do Apache para 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Inicia o Apache em primeiro plano
CMD ["apache2-foreground"]