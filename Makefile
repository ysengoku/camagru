COMPOSE_FILE = ./docker-compose.yml
COMPOSE_FILE_DEV = ./docker-compose.dev.yml
COMPOSE_FILE_PROD = ./docker-compose.prod.yml

APP_CONTAINER = camagru_app
CLIENT_CONTAINER = camagru_client
NGINX_CONTAINER = camagru_nginx
DB_CONTAINER = camagru_database

ENV = development

all: ENV=production
all: up

init-ip:
	@if [ "$(ENV)" = "development" ]; then \
		bash init-ip.sh --dev; \
	else \
		bash init-ip.sh; \
	fi

build: ENV=production
build: init-ip
	ENV=$(ENV) docker compose -f $(COMPOSE_FILE) -f $(COMPOSE_FILE_PROD) build --no-cache
	@docker image prune -f

build-dev: ENV=development
build-dev: init-ip
	ENV=$(ENV) docker compose -f $(COMPOSE_FILE) -f $(COMPOSE_FILE_DEV) build
	@docker image prune -f

up: ENV=production
up: build
	ENV=$(ENV) docker compose -f $(COMPOSE_FILE) -f $(COMPOSE_FILE_PROD) up --remove-orphans

dev: ENV=development
dev: build-dev
	ENV=$(ENV) docker compose -f $(COMPOSE_FILE) -f $(COMPOSE_FILE_DEV) up --remove-orphans

down: 
	docker compose -f $(COMPOSE_FILE) -f $(COMPOSE_FILE_DEV) -f $(COMPOSE_FILE_PROD) down --remove-orphans

clean:
	docker compose -f $(COMPOSE_FILE) -f $(COMPOSE_FILE_DEV) -f $(COMPOSE_FILE_PROD) down --rmi local --volumes --remove-orphans

fclean: clean
	rm -rf app/public/assets/.vite
	rm -rf app/public/assets/assets
	rm -f app/public/assets/*.js
	rm -f app/public/assets/*.css


lint-js:
	docker exec $(CLIENT_CONTAINER) npm run lint

format-js:
	docker exec $(CLIENT_CONTAINER) npm run format:fix

lint-php:
	docker exec $(APP_CONTAINER) vendor/bin/phpcs --standard=phpcs.xml src

format-php:
	docker exec $(APP_CONTAINER) vendor/bin/phpcbf --standard=phpcs.xml src

psalm:
	docker exec $(APP_CONTAINER) vendor/bin/psalm

quality-php:
	$(MAKE) format-php
	$(MAKE) psalm

psalm-baseline:
	docker exec $(APP_CONTAINER) vendor/bin/psalm --update-baseline

test-php:
	docker exec $(APP_CONTAINER) vendor/bin/phpunit --testdox --display-warnings

populate-db:
	docker exec $(APP_CONTAINER) php scripts/populateDb.php

clean-db:
	docker exec $(APP_CONTAINER) php scripts/cleanDb.php

api-doc:
	php app/scripts/apiDoc.php

.PHONY: all dev up down build build-dev init-ip clean fclean lint-js format-js lint-php format-php psalm quality-php test-app clean-db populate-db