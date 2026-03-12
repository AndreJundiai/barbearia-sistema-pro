# Valida se o APP_KEY existe e tem tamanho suficiente (mínimo 32 caracteres para AES-256)
if [[ -z "$APP_KEY" || ${#APP_KEY} -lt 32 ]]; then
    echo "APP_KEY inválida ou ausente. Gerando uma nova..."
    export APP_KEY=$(php artisan key:generate --show)
fi

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
service nginx start
php-fpm
