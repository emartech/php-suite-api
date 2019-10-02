DOCKER = docker
CONTAINER = php-suite-api

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

all: destroy update build run packages ## destroy update build run packages

destroy: ## remove container
	-$(DOCKER) rm -f $(CONTAINER)

build: ## build comtainer
	$(DOCKER) build --no-cache -t $(CONTAINER) .

run: ## run
	$(DOCKER) run -d -v "$$PWD":/var/www/html/ --rm --name=$(CONTAINER) -h $(CONTAINER).ett.local $(CONTAINER)

stop: ## stop container
	-$(DOCKER) rm -f $(CONTAINER)

restart: stop run  ## restart container and run

ssh: sh  ## get a shell in the container (alias for sh)
sh: ## get a shell in the container
	$(DOCKER) exec -it $(CONTAINER) /bin/bash

logs: ## show logs
	$(DOCKER) logs --follow $(CONTAINER)

test: ## run tests
	$(DOCKER) exec $(CONTAINER) bash -c "cd /var/www/html && vendor/bin/phpunit -c test/phpunit.xml $(FILTERARGS) $(TESTFILE)"

packages: ## install packages
	$(DOCKER) exec -i -t $(CONTAINER) /bin/bash -l -c "composer install 2>&1"

pu: packages-update ## update packages (alias for packages-update)
packages-update: ## update packages
	$(DOCKER) exec -i -t $(CONTAINER) /bin/bash -l -c "composer update 2>&1"

update: ## update container
	$(DOCKER) pull $(shell awk '/^FROM/ { print $$2; exit }' Dockerfile)
