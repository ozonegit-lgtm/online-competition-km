FROM php:8.2-fpm

ARG USER_ID=1000
ARG GROUP_ID=1000

# ติดตั้ง Package ที่ Laravel และ PHP Extension ต้องใช้
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

# ติดตั้ง Composer จาก Official Composer Image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# กำหนดสิทธิ์โฟลเดอร์ Laravel
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["php-fpm"]