#!/bin/sh
set -e

mkdir -p /usr/src/app/public/uploads
chown -R node:node /usr/src/app/public/uploads
chmod 755 /usr/src/app/public/uploads

node <<'EOF'
const net = require('net');
const { MYSQL_DATABASE, DB_PORT } = process.env;

function waitForDb() {
  return new Promise((resolve) => {
    const tryConnect = () => {
      const socket = net.createConnection({ host: MYSQL_DATABASE, port: DB_PORT }, () => {
        socket.destroy();
        resolve();
      });
      socket.on('error', () => {
        console.log('Waiting for database...');
        setTimeout(tryConnect, 2000);
      });
    };
    tryConnect();
  });
};

waitForDb();
EOF

exec "$@"