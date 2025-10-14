FROM php:8.4-fpm AS base

WORKDIR /var/www/html

RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update && apt-get install -y default-mysql-client

COPY . .
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

FROM base AS development

ENTRYPOINT [ "entrypoint.sh" ]
CMD [ "php-fpm" ]

FROM base AS production

RUN rm -rf /var/www/html/tests /var/www/html/.git

ENTRYPOINT [ "entrypoint.sh" ]
CMD [ "php-fpm" ]