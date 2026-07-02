#!/bin/sh
set -e

# Wait for database (use mysqladmin or nc if available)
if [ -n "$DB_HOST" ]; then
  HOST="$DB_HOST"
  PORT="${DB_PORT:-3306}"
  echo "Waiting for database ${HOST}:${PORT}..."
  if command -v mysqladmin >/dev/null 2>&1; then
    until mysqladmin ping -h "$HOST" -P "$PORT" --silent; do
      sleep 2
      printf '.'
    done
    echo "\nDatabase is available."
  else
    echo "No mysqladmin found; skipping DB wait."
  fi
fi

exec "$@"
