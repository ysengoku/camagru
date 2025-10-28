FROM node:24 AS base

WORKDIR /usr/src/app

COPY package*.json ./
RUN npm install

COPY . .
COPY ./entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

FROM base AS development

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD [ "npm", "run", "dev" ]

FROM base AS production

RUN npm ci --only=production
RUN npm run build

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD [ "npm", "run", "start" ]