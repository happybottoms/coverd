#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

# Install git (the php image doesn't have it) which is required by composer
apt-get update -yqq
apt-get install git wget libfreetype6-dev libjpeg62-turbo-dev libpng-dev libzip-dev libpq-dev libsodium-dev postgresql -yqq

docker-php-ext-install gd
docker-php-ext-install sodium
docker-php-ext-install iconv
docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
docker-php-ext-install gd
docker-php-ext-install zip
docker-php-ext-install pdo pdo_pgsql pgsql


wget https://composer.github.io/installer.sig -O - -q | tr -d '\n' > installer.sig
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === file_get_contents('installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php --install-dir=bin --filename=composer
php -r "unlink('composer-setup.php'); unlink('installer.sig');"
php bin/composer self-update --2
php bin/composer install --prefer-source --no-interaction
