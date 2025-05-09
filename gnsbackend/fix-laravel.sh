#!/bin/bash

# limpa cache e otimiza
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan queue:clear 
php artisan optimize

# composer dump-autoload
composer dump-autoload

# permissões de arquivos
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/
