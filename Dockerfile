# ─── Etapa 1: Dependencias PHP ────────────────────────────────────────────────
FROM php:8.4-fpm-alpine AS base

# Extensiones PHP necesarias
RUN apk add --no-cache \
        bash \
        curl \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        zip \
        unzip \
        oniguruma-dev \
        icu-dev \
        git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Instalar Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ─── Etapa 2: Dependencias de producción ──────────────────────────────────────
FROM base AS vendor

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader

# ─── Etapa 3: Build de assets frontend ───────────────────────────────────────
FROM node:22-alpine AS frontend

WORKDIR /app
RUN npm install -g pnpm@9
COPY package.json pnpm-lock.yaml ./
RUN pnpm install --frozen-lockfile --ignore-scripts
COPY . .
RUN pnpm run build

# ─── Etapa 4: Imagen final de producción ──────────────────────────────────────
FROM base AS production

WORKDIR /var/www/html

# Copiar código fuente
COPY . .

# Copiar vendor ya compilado
COPY --from=vendor /var/www/html/vendor ./vendor

# Copiar assets compilados (public/build)
COPY --from=frontend /app/public/build ./public/build

# Permisos de Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Configuración PHP para producción
COPY .docker/php/php-prod.ini /usr/local/etc/php/conf.d/99-prod.ini

# Script de entrada que corre migraciones y caché antes de servir
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
