FROM php:8.2-fpm

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        bcmath \
        exif \
        pcntl \
        intl \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# <<< เพิ่มตรงนี้ >>>
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker/php/entrypoint.sh /usr/local/bin/competition-km-entrypoint

RUN sed -i 's/\r$//' /usr/local/bin/competition-km-entrypoint \
    && chmod 0755 /usr/local/bin/competition-km-entrypoint

WORKDIR /var/www/html

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/competition-km-entrypoint"]
CMD ["php-fpm"]
