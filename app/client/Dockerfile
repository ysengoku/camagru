FROM node:24-slim AS base

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm install

COPY . .


FROM base AS development
EXPOSE 5173
CMD ["npm", "run", "dev"]

FROM base AS build
RUN npm run build
