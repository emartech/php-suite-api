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
	docker-compose down

build: ## build container
	@docker-compose build

up: ## run
	docker-compose up -d
	## $(DOCKER) run -d -v "$$PWD":/var/www/html/ --rm --name=$(CONTAINER) -h $(CONTAINER).ett.local $(CONTAINER)

stop: ## stop container
	docker-compose stop

restart: stop up  ## restart container and run

ssh: sh  ## get a shell in the container (alias for sh)
sh: ## get a shell in the container
	@docker-compose exec web /bin/bash

logs: ## show logs
	@docker-compose logs -f web

test: ## run tests
	docker-compose exec web /bin/bash -l -c "cd /var/www/html && vendor/bin/phpunit -c test/phpunit.xml $(FILTERARGS) $(TESTFILE)"

packages: ## install packages
	docker-compose run --rm web composer install
