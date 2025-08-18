# Imagem oficial do PHP com Apache
FROM php:8.3-apache

# Instala dependências de sistema (git, unzip, libzip-dev) e extensões PHP (zip, pdo_mysql)
# Limpa o cache do apt-get para reduzir o tamanho da imagem
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
 && docker-php-ext-install zip pdo_mysql \
 && rm -rf /var/lib/apt/lists/*

# Habilita o mod_rewrite do Apache, essencial para roteamento
RUN a2enmod rewrite

# Adiciona a diretiva ServerName 
# Define um ServerName padrão para evitar warnings (pode ser sobrescrito por env no runtime)
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia o projeto
COPY . /var/www/html

# Define a pasta public como root do Apache de forma mais específica
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf

# Instala dependências do Composer (agora com as ferramentas necessárias)
WORKDIR /var/www/html
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi

# Exponha uma porta padrão (Railway setará PORT dinamicamente em runtime)
EXPOSE 8080

# Ajusta Apache para usar porta do ambiente (PORT) com fallback em 8080
# Nota: a substituição dinâmica será feita via script de entrada
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf || true
RUN sed -i 's#<VirtualHost \*:80>#<VirtualHost *:${PORT}>#' /etc/apache2/sites-available/000-default.conf || true

# Inicia o Apache em primeiro plano
# EntryPoint que prepara a porta dinâmica antes de subir o Apache
RUN printf '#!/bin/bash\nset -e\n: "${PORT:=8080}"\nsed -i "s/Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf || true\nsed -i "s#<VirtualHost \*:.*>#<VirtualHost *:${PORT}>#" /etc/apache2/sites-available/000-default.conf || true\nexec apache2-foreground\n' > /entrypoint.sh \
 && chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]