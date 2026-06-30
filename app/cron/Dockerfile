FROM php:8.5-cli-alpine

WORKDIR /var/www/html

RUN apk add --no-cache mariadb-client \
	&& docker-php-ext-install pdo pdo_mysql

COPY ./cron/cleanup_unverified_users.php ./cron/cleanup_unverified_users.php
COPY ./src/core/Database.php ./src/core/Database.php
COPY ./src/core/Model.php ./src/core/Model.php
COPY ./src/Models/User.php ./src/Models/User.php
COPY ./wait-for-db.sh /usr/local/bin/wait-for-db.sh
COPY ./cron/crontab /etc/crontabs/root

RUN chmod +x /usr/local/bin/wait-for-db.sh

ENTRYPOINT ["/usr/local/bin/wait-for-db.sh"]
CMD ["crond", "-f", "-l", "2"]
