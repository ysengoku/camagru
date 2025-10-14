FROM mysql:8.0 AS base

COPY init_db.sh /docker-entrypoint-initdb.d/

FROM base AS production

# RUN sleep 5 && chmod 700 /usr/bin/mysql /usr/bin/mysqldump
