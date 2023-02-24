DOCKER_COMPOSE = docker-compose -f docker-compose-development.yml

ifndef TESTMETHOD
FILTERARGS=
else
FILTERARGS=--filter $(TESTMETHOD)
endif

.PHONY: all test destroy update

help: ## help page
	@echo "Targets:"
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/\(.*\):.*##[ \t]*/    \1 ## /' | column -t -s '##'
	@echo

all: destroy update build up packages ## destroy update build run packages

destroy: ## remove container
	$(DOCKER_COMPOSE) down

build: ## build container
	$(DOCKER_COMPOSE) build

up: ## run
	$(DOCKER_COMPOSE) up -d

stop: ## stop container
	$(DOCKER_COMPOSE) stop

restart: stop up  ## restart container and run

ssh: sh  ## get a shell in the container (alias for sh)
sh: ## get a shell in the container
	$(DOCKER_COMPOSE) exec web /bin/bash

logs: ## show logs
	$(DOCKER_COMPOSE) logs -f web

test: ## run tests
	$(DOCKER_COMPOSE) exec -T web /bin/bash -l -c "cd /var/www/html && vendor/bin/phpunit -c test/phpunit.xml $(FILTERARGS) $(TESTFILE)"

packages: ## install packages
	$(DOCKER_COMPOSE) run --rm web composer install

update-packages: ## install packages
	$(DOCKER_COMPOSE) run --rm web composer update
