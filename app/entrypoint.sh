#!/bin/sh
set -e

# Ensure storage directory exists (outside webroot) and symlink in public
STORAGE_DIR=/var/www/storage/media
PUBLIC_DIR=/var/www/html/public
PUBLIC_MEDIA=${PUBLIC_DIR}/media

mkdir -p "$STORAGE_DIR"
chown -R www-data:www-data "$STORAGE_DIR" || true
chmod -R 750 "$STORAGE_DIR" || true

mkdir -p "$PUBLIC_DIR"
# create symlink if missing or broken
if [ -L "$PUBLIC_MEDIA" ]; then
  # if link exists but target missing, recreate
  if [ ! -e "$PUBLIC_MEDIA" ]; then
    rm -f "$PUBLIC_MEDIA" || true
    ln -s "$STORAGE_DIR" "$PUBLIC_MEDIA"
  fi
else
  rm -rf "$PUBLIC_MEDIA" || true
  ln -s "$STORAGE_DIR" "$PUBLIC_MEDIA"
fi

# Wait for database (use mysqladmin or nc if available)
if [ -n "$MYSQL_DATABASE" ]; then
  HOST="$MYSQL_DATABASE"
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