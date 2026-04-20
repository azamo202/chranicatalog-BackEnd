FROM php:8.2-cli

# تثبيت المتطلبات
RUN apt-get update -y && apt-get install -y \
    libzip-dev zip unzip git curl

RUN docker-php-ext-install pdo pdo_mysql zip

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install

# صلاحيات لارافيل
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000