# Imagem oficial do PHP com Apache
FROM php:8.3-apache

# Instala dependências de sistema (git, unzip) e extensões PHP (zip, pdo_mysql)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && docker-php-ext-install zip pdo_mysql

# Habilita o mod_rewrite do Apache, essencial para roteamento
RUN a2enmod rewrite

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia o projeto
COPY . /var/www/html

# Define a pasta public como root do Apache de forma mais específica
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf

# Instala dependências do Composer (agora com as ferramentas necessárias)
WORKDIR /var/www/html
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi

# Exponha a porta 8080
EXPOSE 8080

# Altera a porta padrão do Apache para 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Inicia o Apache em primeiro plano
CMD ["apache2-foreground"]