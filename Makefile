COMPOSE_FILE = ./docker-compose.yml

ENV = development

all:
	ENV = production
	up

up: 
	ENV=$(ENV) docker-compose -f $(COMPOSE_FILE) up --build

down: 
	docker-compose -f $(COMPOSE_FILE) down

clean: down
	@docker system prune -f -a --volumes

fclean: clean
	@docker volume rm $$(docker volume ls -q)

dev: 
	ENV=$(ENV) docker-compose -f $(COMPOSE_FILE) --profile development up --build

lint:
	php -l server/app/**/*.php server/public/index.php

.PHONY: all dev up down clean fclean lint