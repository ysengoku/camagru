FROM nginx:alpine AS base

FROM base AS development

COPY nginx.dev.conf /etc/nginx/conf.d/default.conf
CMD ["nginx", "-g" ,"daemon off;"]

FROM base AS production

COPY nginx.prod.conf /etc/nginx/conf.d/default.conf.template

RUN apk add --no-cache openssl && \
    mkdir -p /etc/nginx/ssl && \
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/nginx/ssl/camagru.key \
    -out /etc/nginx/ssl/camagru.crt \
    -subj "/C=FR/ST=AURA/L=Lyon/O=42/OU=42/CN=localhost/UID=yusengok" \
    && chmod 600 /etc/nginx/ssl/camagru.key /etc/nginx/ssl/camagru.crt

CMD envsubst '$HOST_IP' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf \
    && nginx -g 'daemon off;'
