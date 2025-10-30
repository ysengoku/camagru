FROM mysql:8.0 AS base

COPY init_db.sh /docker-entrypoint-initdb.d/
COPY ./migrations /migrations
RUN chmod +x /docker-entrypoint-initdb.d/init_db.sh

FROM base AS production

# RUN sleep 5 && chmod 700 /usr/bin/mysql /usr/bin/mysqldump
