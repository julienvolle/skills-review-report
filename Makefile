#
# -- HELP --
#

black  = \033[1;230;30m
red    = \033[31m
green  = \033[32m
yellow = \033[33m
orange = \033[34m
purple = \033[35m
cyan   = \033[36m
end    = \033[0m

.DEFAULT_GOAL := help
help:
	@echo ""
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e "s/\[32m##/[33m/"
	@echo ""
.PHONY: help

#
# -- CONST --
#

# Environment
env = dev

# Code coverage required
percent = 100

##
## -- INSTALL --
##

install: ## Install [env=dev]
	@$(MAKE) --no-print-directory install-package-manager
	@$(MAKE) --no-print-directory install-dependencies
	@$(MAKE) --no-print-directory install-assets env=$(env)
	@$(MAKE) --no-print-directory cc-application env=$(env)
	@$(MAKE) --no-print-directory install-database env=$(env)
	@$(MAKE) --no-print-directory cc-database env=$(env)
	@printf "Install fixtures? ${orange}[database will be purged]${end} (y/n) [${yellow}n${end}]: "; \
		read reponse; \
		if [ "$$reponse" = "y" ] ; \
		then \
			$(MAKE) --no-print-directory install-fixtures env=$(env) ; \
		fi;
	@printf '\n${green}[OK]${end} Install finished\n\n'
	@php bin/console about
.PHONY: install

##

install-package-manager: ## Install package manager
	@printf "Install or upgrade composer? ${orange}[root permissions required]${end} (y/n) [${yellow}n${end}]: "; \
		read reponse; \
		if [ "$$reponse" = "y" ] ; \
		then \
			printf '\n' ; \
			composer self-update --2 ; \
			printf '\n' ; \
		fi;
	@printf "Install or upgrade npm? ${orange}[root permissions required]${end} (y/n) [${yellow}n${end}]: "; \
		read reponse; \
		if [ "$$reponse" = "y" ] ; \
		then \
			npm install -g npm@latest --no-audit --no-fund ; \
			printf '\n' ; \
			echo "npm --version" ; \
			npm --version ; \
			printf '\n' ; \
		fi;
	@printf "Install or upgrade yarn (with npm)? ${orange}[root permissions required]${end} (y/n) [${yellow}n${end}]: "; \
		read reponse; \
		if [ "$$reponse" = "y" ] ; \
		then \
			npm install -g yarn@latest --no-audit --no-fund ; \
			printf '\n' ; \
			echo "yarn --version" ; \
			yarn --version ; \
			printf '\n' ; \
			else \
				printf '\n' ; \
		fi;
.PHONY: install-package-manager

install-dependencies: ## Install dependencies
	composer clear-cache --no-interaction
	@printf '\n'
	@if [ "$(no-install)" != "1" ]; then \
		echo "composer install --no-interaction --prefer-dist" ; \
		composer install --no-interaction --prefer-dist ; \
	fi;
	composer dump-autoload --no-interaction
	@printf '\n'
.PHONY: install-assets

install-assets: ## Install assets [env=dev]
	npm cache clean --force
	@printf '\n'
	npm cache verify
	@printf '\n'
	yarn cache clean
	@printf '\n'
	@if [ "$(no-install)" != "1" ]; then \
		echo "yarn install --force" ; \
		yarn install --force ; \
		printf '\n' ; \
	fi;
	yarn encore $(env)
	@printf '\n'
.PHONY: install-assets

install-database: ## Install database [env=dev]
	php bin/console doctrine:database:create --env=$(env) --if-not-exists
	@printf '\n'
	php bin/console doctrine:migrations:migrate --env=$(env) --no-interaction -vv
	@$(MAKE) --no-print-directory test-database env=$(env)
.PHONY: install-database

install-database-test: ## Install database for testing
	rm -rf var/sqlite
	mkdir -p var/sqlite
	@printf '\n'
	php bin/console doctrine:database:create --env=test
	@printf '\n'
	php bin/console doctrine:schema:update --env=test --force -n
	php bin/console doctrine:fixtures:load --env=test --no-interaction -vv
	@printf '\n'
.PHONY: install-database-test

install-fixtures: ## Install fixtures [env=dev]
	php bin/console doctrine:fixtures:load --env=$(env) --no-interaction -vv
.PHONY: install-fixtures

##

reset: ## Reset install [env=dev]
	@printf "Drop database?(y/n) [${yellow}n${end}]: "; \
		read reponse; \
		if [ "$$reponse" = "y" ] ; \
		then \
			php bin/console doctrine:database:drop --env=$(env) --force --if-exists ; \
		fi;
	@printf '\n'
	rm -rf public/build
	rm -rf node_modules
	rm -rf var
	rm -rf vendor
	rm -f .php-cs-fixer.cache
	rm -f phpcs.cache
	rm -f yarn-error.log
	@printf '\n'
	@printf "Install?(y/n) [${yellow}n${end}]: "; \
		read reponse; \
		if [ "$$reponse" = "y" ] ; \
		then \
			$(MAKE) --no-print-directory install env=$(env) ; \
		fi;
.PHONY: reset

##
## -- CACHE --
##

cc: ## Clear Cache [env=dev]
	@$(MAKE) --no-print-directory cc-package-manager
	@$(MAKE) --no-print-directory cc-application env=$(env)
	@$(MAKE) --no-print-directory cc-database env=$(env)
.PHONY: cc

##

cc-package-manager: ## Clear Cache Package Manager
	@$(MAKE) --no-print-directory install-dependencies no-install=1
	@$(MAKE) --no-print-directory install-assets no-install=1
.PHONY: cc-package-manager

cc-application: ## Clear Cache Application [env=dev]
	php bin/console cache:clear --env=$(env) --no-warmup --no-interaction -vv
	php bin/console cache:pool:prune --env=$(env) --no-interaction -vv
	php bin/console cache:warmup --env=$(env) --no-interaction -vv
.PHONY: cc-application

cc-database: ## Clear Cache Database [env=dev]
	php bin/console doctrine:cache:clear-metadata --env=$(env) --flush
	php bin/console doctrine:cache:clear-query --env=$(env) --flush
	php bin/console doctrine:cache:clear-result --env=$(env) --flush
.PHONY: cc-database

##
## -- TEST --
##

test: ## Test
	@printf "Run code fixer? (y/n) [${yellow}n${end}]: "; \
		read reponse; \
		if [ "$$reponse" = "y" ] ; \
		then \
			printf '\n' ; \
			$(MAKE) --no-print-directory phpcbf ; \
			$(MAKE) --no-print-directory phpcsf ; \
			else \
				printf '\n' ; \
		fi;
	@$(MAKE) --no-print-directory cc-application env=test
	@$(MAKE) --no-print-directory cc-database env=test
	@$(MAKE) --no-print-directory test-database
	@$(MAKE) --no-print-directory test-container
	@$(MAKE) --no-print-directory test-deprecations
	@$(MAKE) --no-print-directory test-yaml
	@$(MAKE) --no-print-directory test-twig
	@$(MAKE) --no-print-directory test-php
	@printf "Test code coverage? (y/n) [${yellow}n${end}]: "; \
		read reponse; \
		if [ "$$reponse" = "y" ] ; \
		then \
			printf '\n' ; \
			$(MAKE) --no-print-directory test-code-coverage ; \
			else \
				printf '\n' ; \
				$(MAKE) --no-print-directory test-unit ; \
				$(MAKE) --no-print-directory test-functional ; \
		fi;
.PHONY: test

##

test-database: ## Test database [env=dev]
	php bin/console doctrine:schema:validate --env=$(env) --no-interaction -vv
	php bin/console doctrine:mapping:info --env=$(env) --no-interaction -vv
	@printf '\n'
.PHONY: test-database

test-container: ## Test container
	php bin/console lint:container
.PHONY: test-container

test-deprecations: ## Test deprecations
	php bin/console debug:container --deprecations
.PHONY: test-deprecations

test-yaml: ## Test yaml files
	php bin/console lint:yaml --parse-tags config templates
.PHONY: test-yaml

test-twig: ## Test twig files
	php bin/console lint:twig templates
	php vendor/bin/twigcs --config=twigcs.dist.php
	@printf '\n'
.PHONY: test-twig

test-php: ## Test php files
	@$(MAKE) --no-print-directory phpcs
	@$(MAKE) --no-print-directory phpmd
	@printf '\n'
.PHONY: test-php

test-unit: ## Test unit
	php -dxdebug.mode=coverage -dxdebug.profiler_enable=off vendor/bin/phpunit --stop-on-failure --verbose --testdox --group=unit
	@printf '\n'
.PHONY: test-unit

test-functional: ## Test functional
	@$(MAKE) --no-print-directory install-database-test
	php -dxdebug.mode=coverage -dxdebug.profiler_enable=off vendor/bin/phpunit --stop-on-failure --verbose --testdox --group=functional
	@printf '\n'
.PHONY: test-functional

test-code-coverage: ## Test code coverage [percent=100]
	@$(MAKE) --no-print-directory install-database-test
	php -dxdebug.mode=coverage -dxdebug.profiler_enable=off vendor/bin/phpunit --stop-on-failure --verbose --group=unit,functional --coverage-clover=var/cache/phpunit/clover.xml
	@printf '\n'
	php vendor/bin/coverage-check var/cache/phpunit/clover.xml $(percent)
	@printf '\n'
.PHONY: test-code-coverage

##
## -- TOOL --
##

phpcs: ## Run PHP Code Sniffer
	php vendor/bin/phpcs --config-set installed_paths vendor/phpcompatibility/php-compatibility
	php vendor/bin/phpcs -p --standard=phpcs.xml.dist
.PHONY: phpcs

phpmd: ## Run PHP Mess Detector
	php vendor/bin/phpmd src text ./phpmd.xml.dist
.PHONY: phpmd

phpcbf: ## Run PHP Code Beautifier & Fixer
	php vendor/bin/phpcbf --extensions=php --standard=PSR1,PSR12 -p config migrations public src tests
.PHONY: phpcbf

phpcsf: ## Run PHP Code Sniffer Fixer
	php vendor/bin/php-cs-fixer fix --config='.php-cs-fixer.dist.php' --verbose
	@printf '\n'
.PHONY: phpcsf

##

report-coverage: ## Create coverage HTML report
	@$(MAKE) --no-print-directory install-database-test
	php -dxdebug.mode=coverage -dxdebug.profiler_enable=off vendor/bin/phpunit --stop-on-failure --verbose --group=unit,functional --coverage-html=var/cache/phpunit/html
	@printf '\nHTML report generated in "var/cache/phpunit/html" directory\n\n'
.PHONY: report-coverage

report-metrics: ## Create metrics report
	php vendor/bin/phpmetrics --report-html=var/cache/phpmetrics src
.PHONY: report-metrics
