DOCKER = docker
CONTAINER = php-suite-api

ifndef TESTMETHOD
FILTERARGS=
else
FILTERARGS=--filter $(TESTMETHOD)
endif

.PHONY: all test destroy update

all: destroy update build run packages

destroy:
	-$(DOCKER) rm -f $(CONTAINER)

build:
	$(DOCKER) build --no-cache -t $(CONTAINER) .

run:
	$(DOCKER) run -d -v "$$PWD":/var/www/html/ --rm --name=$(CONTAINER) -h $(CONTAINER).ett.local $(CONTAINER)

stop:
	-$(DOCKER) rm -f $(CONTAINER)
	-$(DOCKER) rm -f $(DB_CONTAINER)

restart: stop run

ssh: sh
sh:
	$(DOCKER) exec -it $(CONTAINER) /bin/bash

logs:
	$(DOCKER) logs --follow $(CONTAINER)

test:
	$(DOCKER) exec $(CONTAINER) bash -c "cd /var/www/html && vendor/bin/phpunit -c test/phpunit.xml $(FILTERARGS) $(TESTFILE)"

packages:
	$(DOCKER) exec -i -t $(CONTAINER) /bin/bash -l -c "composer install 2>&1"

pu: packages-update
packages-update:
	$(DOCKER) exec -i -t $(CONTAINER) /bin/bash -l -c "composer update 2>&1"

update:
	$(DOCKER) pull $(shell awk '/^FROM/ { print $$2; exit }' Dockerfile)
