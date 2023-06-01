#!/bin/sh
mkdir -p ./public/doc 
export http_proxy='http://10.0.0.1:80'
composer require --dev asset symfony/apache-pack debug templates symfony/ux-turbo symfony/profiler-pack symfony/var-dumper orm-fixtures
yarn add bootstrap jquery @popperjs/core @fontsource/roboto-condensed @fortawesome/fontawesome-free axios core-js webpack encore webpack-cli webpack-notifier @symfony/webpack-encore --dev
composer install
composer update 
yarn install
composer clear-cache
chmod 777 . -R -v
# yarn encore production &
php bin/console make:migration

exec apache2-foreground  &
# yarn watch &
yarn encore dev --watch