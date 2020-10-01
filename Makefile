# === Makefile Helper ===

# Styles
YELLOW=$(shell echo "\033[00;33m")
RED=$(shell echo "\033[00;31m")
RESTORE=$(shell echo "\033[0m")

# Variables
PHP_BIN := php
COMPOSER := composer
CURRENT_DIR := $(shell pwd)
.DEFAULT_GOAL := list
SYMFONY := symfony
EZ_DIR := $(CURRENT_DIR)/ezplatform
DOCKER_DB_CONTAINER := dbalgoliaezbundle
MYSQL := mysql

.PHONY: list
list:
	@echo "******************************"
	@echo "${YELLOW}Available targets${RESTORE}:"
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | sort | awk 'BEGIN {FS = ":.*?## "}; {printf " ${YELLOW}%-15s${RESTORE} > %s\n", $$1, $$2}'
	@echo "${RED}==============================${RESTORE}"

.PHONY: installez
installez:  ## Install eZ as the local project
	@docker run -d -p 3355:3306 --name $(DOCKER_DB_CONTAINER) -e MYSQL_ROOT_PASSWORD=ezplatform mariadb:10.3
	@COMPOSER_MEMORY_LIMIT=-1 composer create-project ezsystems/ezplatform-ee --prefer-dist --no-progress --no-interaction --no-scripts --repository=https://updates.ez.no/bul/  $(EZ_DIR)
	@curl -o tests/provisioning/wrap.php https://raw.githubusercontent.com/Plopix/symfony-bundle-app-wrapper/master/wrap-bundle.php
	@WRAP_APP_DIR=./ezplatform WRAP_BUNDLE_DIR=./ php tests/provisioning/wrap.php
	@rm tests/provisioning/wrap.php
	@mkdir -p $(EZ_DIR)/node_modules && ln -s $(EZ_DIR)/node_modules
	@cd $(EZ_DIR) && yarn add --dev react react-dom react-instantsearch-dom algoliasearch
	@echo "DATABASE_URL=mysql://root:ezplatform@127.0.0.1:3355/ezplatform" >>  $(EZ_DIR)/.env.local
	@cd $(EZ_DIR) && composer update --lock
	@cd $(EZ_DIR) && composer ezplatform-install
	@cd $(EZ_DIR) && bin/console cache:clear

.PHONY: serveez
serveez: stopez ## Clear the cache and start the web server
	@cd $(EZ_DIR) && rm -rf var/cache/*
	@docker start $(DOCKER_DB_CONTAINER)
	@cd $(EZ_DIR) && bin/console cache:clear
	@cd $(EZ_DIR) && $(SYMFONY) local:server:start -d

.PHONY: ps
ps: ## Show docker-compose services
	@cd $(EZ_DIR) && $(SYMFONY) server:status

.PHONY: stopez
stopez: ## Stop the web server if it is running
	@cd $(EZ_DIR) && $(SYMFONY) local:server:stop
	@docker stop $(DOCKER_DB_CONTAINER)


.PHONY: codeclean
codeclean: ## Coding Standard checks
	$(PHP_BIN) ./vendor/bin/php-cs-fixer fix --config=.cs/.php_cs.php
	$(PHP_BIN) ./vendor/bin/phpcs --standard=.cs/cs_ruleset.xml --extensions=php bundle tests
	$(PHP_BIN) ./vendor/bin/phpmd bundle,tests text .cs/md_ruleset.xml

.PHONY: tests
tests: ## Run the tests
	DATABASE_URL="mysql://root:ezplatform@127.0.0.1:3355/ezplatform" $(PHP_BIN) ./vendor/bin/phpunit -c "tests" "tests"

.PHONY: install
install: ## Install vendors
	$(COMPOSER) install

.PHONY: clean
clean: ## Removes the vendors, and caches
	@rm -f .php_cs.cache
	@rm -rf vendor
	@rm -rf ezplatform
	@rm -rf node_modules
	@rm -f composer.lock
	@docker stop $(DOCKER_DB_CONTAINER)
	@docker rm $(DOCKER_DB_CONTAINER)
