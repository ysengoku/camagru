FROM php:8.5-fpm AS base

WORKDIR /var/www/html

RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update && apt-get install -y --no-install-recommends default-mysql-client ca-certificates \
	&& rm -rf /var/lib/apt/lists/*

COPY . .
COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

FROM base AS development

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD [ "php-fpm" ]


FROM base AS production

RUN rm -rf /var/www/html/tests /var/www/html/.git

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD [ "php-fpm" ]