#!/bin/bash
set -e

export MYSQL_PWD="$MYSQL_ROOT_PASSWORD"

# Create the database and user
mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;"
mysql -u root -e "CREATE USER IF NOT EXISTS '${MYSQL_USER}' IDENTIFIED BY '${MYSQL_PASSWORD}';"
mysql -u root -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${MYSQL_USER}';"
# Activate the changes
mysql -u root -e "FLUSH PRIVILEGES;"
# Set the root password
mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '"$MYSQL_ROOT_PASSWORD"';"
