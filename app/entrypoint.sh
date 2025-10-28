#!/bin/sh
set -e

mkdir -p /usr/src/app/public/uploads
chown -R node:node /usr/src/app/public/uploads
chmod 755 /usr/src/app/public/uploads

node <<'EOF'
const mysql = require('mysql2/promise');
const { DB_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE } = process.env;

(async function waitForDb() {
  while (1) {
    try {
      const conn = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.MYSQL_USER,
        password: process.env.MYSQL_PASSWORD,
        database: process.env.MYSQL_DATABASE,
      });
      await conn.end();
      console.log('Database is ready');
      break;
    } catch {
      console.log('Waiting for Database...');
      await new Promise(r => setTimeout(r, 2000));
    }
  }
})();
EOF

exec "$@"