#!/bin/bash
set -e

echo "Initializing database and applying migrations..."

mysql -u root -p"$MYSQL_ROOT_PASSWORD" <<EOSQL
	USE \`${MYSQL_DATABASE}\`;
	SOURCE /migrations/001_initial_schema.sql;
EOSQL

echo "Migration completed successfully."

if [ -n "$MYSQL_TEST_DATABASE" ]; then
	echo "Creating and migrating database for testing..."

	mysql -u root -p"$MYSQL_ROOT_PASSWORD" <<EOSQL
		CREATE DATABASE IF NOT EXISTS \`${MYSQL_TEST_DATABASE}\`;
		USE \`${MYSQL_TEST_DATABASE}\`;
		GRANT ALL PRIVILEGES ON \`${MYSQL_TEST_DATABASE}\`.* TO '${MYSQL_USER}'@'%';
		FLUSH PRIVILEGES;
		SOURCE /migrations/001_initial_schema.sql;

EOSQL
	
	echo "Creation and migrating database for testing completed successfully."
fi
