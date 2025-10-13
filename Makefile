COMPOSE_FILE = ./docker-compose.yml
COMPOSE_FILE_DEV = ./docker-compose.dev.yml
COMPOSE_FILE_PROD = ./docker-compose.prod.yml

ENV = development

all: ENV=production
all: up

up: 
	ENV=$(ENV) docker compose -f $(COMPOSE_FILE) -f $(COMPOSE_FILE_PROD) up --build

down: 
	docker compose -f $(COMPOSE_FILE) down

clean: down
	@docker system prune -f -a --volumes

fclean: clean
	@docker volume rm $$(docker volume ls -q)

dev: 
	ENV=$(ENV) docker compose -f $(COMPOSE_FILE) -f $(COMPOSE_FILE_DEV) --profile development up --build

lint:
	php -l server/app/**/*.php server/public/index.php

.PHONY: all dev up down clean fclean lint