FROM php:8.5-fpm AS base

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
	default-mysql-client \
	ca-certificates \
	# --- GD Extension Dependencies ---
    # libpng-dev: Required for processing PNG images
    libpng-dev \
    # libjpeg-dev: Required for processing JPEG images
    libjpeg-dev \
    # libfreetype6-dev: Required for rendering TrueType fonts (imagettftext)
    libfreetype6-dev \
	# Configure the GD extension to compile with FreeType and JPEG support enabled
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	# Install pdo, pdo_mysql, and the configured gd extension into PHP
	&& docker-php-ext-install pdo pdo_mysql gd\
	&& rm -rf /var/lib/apt/lists/*

COPY . .
COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh
COPY ./wait-for-db.sh /usr/local/bin/wait-for-db.sh
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/wait-for-db.sh

FROM base AS development

RUN apt-get update && apt-get install -y --no-install-recommends \
	git \
	libzip-dev \
	unzip \
	&& docker-php-ext-install zip \
	&& rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN DISABLE_HTTP2=1 composer install --no-interaction --prefer-dist

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD [ "php-fpm" ]


FROM base AS production

# Adopts PHP's own recommended production settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN rm -rf /var/www/html/tests /var/www/html/.git

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD [ "php-fpm" ]
