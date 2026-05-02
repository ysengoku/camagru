COMPOSE_FILE = ./docker-compose.yml
COMPOSE_FILE_DEV = ./docker-compose.dev.yml
COMPOSE_FILE_PROD = ./docker-compose.prod.yml

APP_CONTAINER = camagru_app
NGINX_CONTAINER = camagru_nginx
DB_CONTAINER = camagru_database

ENV = development

all: ENV=production
all: up

ensure-env:
	@if [ ! -f .env ]; then \
		echo ".env file not found, copying from .env.example"; \
		cp .env.example .env; \
	fi

init-ip:
	bash init-ip.sh

build: ensure-env init-ip
	ENV=$(ENV) docker compose -f $(COMPOSE_FILE) -f $(COMPOSE_FILE_PROD) build --no-cache

up: build
	ENV=$(ENV) docker compose -f $(COMPOSE_FILE) -f $(COMPOSE_FILE_PROD) up

down: 
	docker compose -f $(COMPOSE_FILE) down

clean: down
	@docker system prune -f -a --volumes

fclean: clean
	@docker volume rm $$(docker volume ls -q)

build-dev: ensure-env init-ip
	ENV=$(ENV) docker compose -f $(COMPOSE_FILE) -f $(COMPOSE_FILE_DEV) --profile development build --no-cache

dev: build-dev
	ENV=$(ENV) docker compose -f $(COMPOSE_FILE) -f $(COMPOSE_FILE_DEV) --profile development up

test-app:
	docker exec $(APP_CONTAINER) npm run test

lint-php:
	docker exec $(APP_CONTAINER) vendor/bin/phpcs --standard=phpcs.xml src

fix-php:
	docker exec $(APP_CONTAINER) vendor/bin/phpcbf --standard=phpcs.xml src

.PHONY: all dev up down clean fclean lint-php fix-php