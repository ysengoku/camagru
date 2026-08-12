FROM nginx:alpine AS base

FROM base AS development

COPY nginx.dev.conf /etc/nginx/conf.d/default.conf.template
CMD envsubst '$HOST_IP $APP_HOST' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf \
    && nginx -g 'daemon off;'


FROM base AS production

COPY nginx.prod.conf /etc/nginx/conf.d/default.conf.template

RUN apk add --no-cache openssl && mkdir -p /etc/nginx/ssl

CMD mkdir -p /etc/nginx/ssl && \
    if [ "$APP_HOST" != "$HOST_IP" ]; then \
        SAN="IP:${HOST_IP},DNS:${APP_HOST}"; \
    else \
        SAN="IP:${HOST_IP}"; \
    fi && \
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/nginx/ssl/camagru.key \
    -out /etc/nginx/ssl/camagru.crt \
    -subj "/C=FR/ST=AURA/L=Lyon/O=42/OU=42/CN=${HOST_IP}/UID=yusengok" \
    -addext "subjectAltName=${SAN}" \
    && chmod 600 /etc/nginx/ssl/camagru.key /etc/nginx/ssl/camagru.crt \
    && envsubst '$HOST_IP $APP_HOST' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf \
    && nginx -g 'daemon off;'
