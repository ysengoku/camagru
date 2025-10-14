#!/bin/bash
set -e

mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "USE \`${MYSQL_DATABASE}\`;"