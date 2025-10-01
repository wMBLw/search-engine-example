FROM php:8.3-fpm

LABEL authors="wMBLw"

RUN apt-get update && apt-get install -y \
    curl \
    nano \
    git \
    unzip \
    zip \
    ca-certificates \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y cron \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y nginx \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    zip \
    soap \
    xml \
    curl \
    intl \
    opcache \
    pcntl

RUN pecl install redis xdebug && docker-php-ext-enable redis xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && composer self-update

RUN echo "user=root" >> /etc/supervisor/supervisord.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY php.ini /usr/local/etc/php/php.ini

COPY nginx.conf /etc/nginx/nginx.conf

COPY default.conf /etc/nginx/conf.d/default.conf

RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

RUN mkdir -p /var/www/html
WORKDIR /var/www/html

RUN echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" >> /etc/crontab \
    && chmod 0644 /etc/crontab

EXPOSE 80

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
