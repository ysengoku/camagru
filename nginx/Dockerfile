FROM nginx:alpine AS base

ARG ENV=dev

COPY nginx.${ENV}.conf /etc/nginx/conf.d/default.conf


FROM base AS development

CMD ["nginx", "-g" ,"daemon off;"]

FROM base AS production

COPY ssl/server.crt /etc/ssl/certs/
COPY ssl/server.key /etc/ssl/private/

CMD ["nginx", "-g" ,"daemon off;"]
