FROM php:8.4-cli-bookworm

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip curl \
    && install-php-extensions pdo_pgsql pgsql intl gd zip bcmath opcache pcntl exif \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY php.ini /usr/local/etc/php/conf.d/zz-educoeur.ini

RUN mkdir -p /tmp/opcache && chmod 777 /tmp/opcache

WORKDIR /var/www/html

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
