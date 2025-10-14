#!/bin/sh
set -e

mkdir -p /var/www/html/public/uploads
chown -R www-data:www-data /var/www/html/public/uploads
chmod 755 /var/www/html/public/uploads

until mysqladmin ping -h"$DB_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --ssl=0 --silent; do
  echo "Waiting for database..."
  sleep 2
done

php init.php

exec "$@"