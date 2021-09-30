FROM php:8-apache-buster
RUN a2enmod rewrite

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === '756890a4488ce9024fc62c56153228907f1545c228516cbf63f885e036d37e9a59d27d63f46af1d4d07ee0f76181c7d3') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

RUN apt-get update && apt-get install -y --no-install-recommends \
    gnupg gosu ca-certificates net-tools \
    curl wget mc htop curl sudo nano vim \
    libzip-dev zip unzip p7zip-full git \
    libgmp-dev

RUN docker-php-ext-install zip pdo pdo_mysql gmp opcache && docker-php-ext-enable opcache
RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN rm -rf /var/lib/apt/lists/*

WORKDIR /usr/local/etc/php
RUN cp php.ini-development php.ini
RUN echo "xdebug.mode=debug" >> php.ini
#RUN echo "xdebug.start_with_request=yes" >> php.ini

ENV APACHE_DOCUMENT_ROOT /app
RUN addgroup --gid 1000 laravel && adduser --gecos laravel --ingroup laravel --shell /bin/sh --disabled-password --no-create-home laravel
RUN mkdir -p ${APACHE_DOCUMENT_ROOT}
RUN chown laravel:laravel ${APACHE_DOCUMENT_ROOT}
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
WORKDIR ${APACHE_DOCUMENT_ROOT}
