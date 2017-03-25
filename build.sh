#!/bin/sh

npm install
composer install
php artisan migrate
./node_modules/.bin/gulp
