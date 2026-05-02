FROM php:8.5-fpm AS base

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
	default-mysql-client \
	ca-certificates \
	&& docker-php-ext-install pdo pdo_mysql \
	&& rm -rf /var/lib/apt/lists/*

COPY . .
COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

FROM base AS development

RUN apt-get update && apt-get install -y --no-install-recommends \
	git \
	libzip-dev \
	unzip \
	&& docker-php-ext-install zip \
	&& rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-interaction --prefer-dist

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD [ "php-fpm" ]


FROM base AS production

RUN rm -rf /var/www/html/tests /var/www/html/.git

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD [ "php-fpm" ]