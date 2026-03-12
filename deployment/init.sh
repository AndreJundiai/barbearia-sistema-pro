# Valida se o APP_KEY existe e parece ser uma chave Laravel válida (base64:...)
if [[ -z "$APP_KEY" || ! "$APP_KEY" =~ ^base64: ]]; then
    echo "APP_KEY inválida ou ausente. Gerando uma nova via PHP puro..."
    export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
fi

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
service nginx start
php-fpm
