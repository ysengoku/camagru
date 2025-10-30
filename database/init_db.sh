#!/bin/bash
set -e

echo "Initializing database and applying migrations..."

mysql -u root -p"$MYSQL_ROOT_PASSWORD" <<EOSQL
	USE \`${MYSQL_DATABASE}\`;
	SOURCE /migrations/001_initial_schema.sql;
EOSQL

echo "Migration completed successfully."