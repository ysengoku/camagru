#!/bin/sh
set -e

mkdir -p /usr/src/app/public/uploads
chown -R /usr/src/app/public/uploads
chmod 755 /usr/src/app/public/uploads

until mysqladmin ping -h"$DB_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --ssl=0 --silent; do
  echo "Waiting for database..."
  sleep 2
done

exec "$@"