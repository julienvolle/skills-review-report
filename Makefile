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

# environment
env=dev

# user:group
user=root

# container name
container=php

# min code coverage required
percent=100

# target command inside or outside docker container
cmd=docker-compose exec -u $(user) $(container)
ifeq ($(shell docker images -q ${NAME} 2> /dev/null),)
	cmd=
endif

##
## -- INSTALL --
##

install: ## Install [env=dev]
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
	@$(MAKE) --no-print-directory about
.PHONY: install

##

install-dependencies: ## Install dependencies
	$(cmd) composer clear-cache --no-interaction
	@printf '\n'
	@if [ "$(no-install)" != "1" ]; then \
		echo "composer install --no-interaction --prefer-dist" ; \
		$(cmd) composer install --no-interaction --prefer-dist ; \
	fi;
	$(cmd) composer dump-autoload --no-interaction
	@printf '\n'
.PHONY: install-assets

install-assets: ## Install assets [env=dev]
	$(cmd) npm cache clean --force
	@printf '\n'
	$(cmd) npm cache verify
	@printf '\n'
	$(cmd) yarn cache clean
	@printf '\n'
	@if [ "$(no-install)" != "1" ]; then \
		echo "yarn install --force" ; \
		$(cmd) yarn install --force ; \
		printf '\n' ; \
	fi;
	$(cmd) yarn run build
	@printf '\n'
.PHONY: install-assets

install-database: ## Install database [env=dev]
	@if [ "$(env)" != "test" ]; then \
		$(cmd) php bin/console doctrine:database:create --env=$(env) --if-not-exists ; \
		printf '\n' ; \
		$(cmd) php bin/console doctrine:migrations:migrate --env=$(env) --no-interaction -vv ; \
		$(MAKE) --no-print-directory test-database env=$(env) ; \
		else \
			$(cmd) rm -rf var/sqlite ; \
			$(cmd) mkdir -p var/sqlite ; \
			printf '\n' ; \
			$(cmd) php bin/console doctrine:database:create --env=test ; \
			printf '\n' ; \
			$(cmd) php bin/console doctrine:schema:update --env=test --force -n --complete ; \
			$(cmd) php bin/console doctrine:fixtures:load --env=test --no-interaction -vv ; \
			printf '\n' ; \
	fi;
.PHONY: install-database

install-fixtures: ## Install fixtures [env=dev]
	$(cmd) php bin/console doctrine:fixtures:load --env=$(env) --no-interaction -vv
.PHONY: install-fixtures

##

reset: ## Reset install [env=dev]
	@printf "Drop database?(y/n) [${yellow}n${end}]: "; \
		read reponse; \
		if [ "$$reponse" = "y" ] ; \
		then \
			$(cmd) php bin/console doctrine:database:drop --env=$(env) --force --if-exists ; \
		fi;
	@printf '\n'
	$(cmd) rm -rf public/build
	$(cmd) rm -rf node_modules
	$(cmd) rm -rf var
	$(cmd) rm -rf vendor
	$(cmd) rm -f .php-cs-fixer.cache
	$(cmd) rm -f phpcs.cache
	$(cmd) rm -f yarn-error.log
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
	$(cmd) php bin/console cache:clear --env=$(env) --no-warmup --no-interaction -vv
	$(cmd) php bin/console cache:pool:prune --env=$(env) --no-interaction -vv
	$(cmd) php bin/console cache:warmup --env=$(env) --no-interaction -vv
.PHONY: cc-application

cc-database: ## Clear Cache Database [env=dev]
	$(cmd) php bin/console doctrine:cache:clear-metadata --env=$(env) --flush
	$(cmd) php bin/console doctrine:cache:clear-query --env=$(env) --flush
	$(cmd) php bin/console doctrine:cache:clear-result --env=$(env) --flush
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
	$(cmd) php bin/console doctrine:schema:validate --env=$(env) --no-interaction -vv
	$(cmd) php bin/console doctrine:mapping:info --env=$(env) --no-interaction -vv
	@printf '\n'
.PHONY: test-database

test-container: ## Test container
	$(cmd) php bin/console lint:container
.PHONY: test-container

test-deprecations: ## Test deprecations
	$(cmd) php bin/console debug:container --deprecations
.PHONY: test-deprecations

test-yaml: ## Test yaml files
	$(cmd) php bin/console lint:yaml --parse-tags config templates
.PHONY: test-yaml

test-twig: ## Test twig files
	$(cmd) php bin/console lint:twig templates
	$(cmd) php vendor/bin/twigcs --config=twigcs.dist.php
	@printf '\n'
.PHONY: test-twig

test-php: ## Test php files
	@$(MAKE) --no-print-directory phpcs
	@$(MAKE) --no-print-directory phpmd
	@printf '\n'
.PHONY: test-php

test-unit: ## Test unit
	$(cmd) php -dxdebug.mode=coverage -dxdebug.profiler_enable=off vendor/bin/phpunit \
		--stop-on-failure \
		--verbose \
		--testdox \
		--group=unit
	@printf '\n'
.PHONY: test-unit

test-functional: ## Test functional
	@$(MAKE) --no-print-directory install-database env=test
	$(cmd) php -dxdebug.mode=coverage -dxdebug.profiler_enable=off vendor/bin/phpunit \
		--stop-on-failure \
		--verbose \
		--testdox \
		--group=functional
	@printf '\n'
.PHONY: test-functional

test-code-coverage: ## Test code coverage [percent=100]
	@$(MAKE) --no-print-directory install-database env=test
	$(cmd) php -dxdebug.mode=coverage -dxdebug.profiler_enable=off vendor/bin/phpunit \
		--stop-on-failure \
		--verbose \
		--group=unit,functional \
		--log-junit=var/cache/phpunit/phpunit_junit.xml \
		--coverage-clover=var/cache/phpunit/clover.xml
	@printf '\n'
	php vendor/bin/coverage-check var/cache/phpunit/clover.xml $(percent)
	@printf '\n'
.PHONY: test-code-coverage

##
## -- TOOL --
##

about: ## Display information about the current project
	$(cmd) php bin/console about
.PHONY: about

##

phpcs: ## Run PHP Code Sniffer
	$(cmd) php vendor/bin/phpcs --config-set installed_paths vendor/phpcompatibility/php-compatibility
	$(cmd) php vendor/bin/phpcs -p --standard=phpcs.xml.dist
.PHONY: phpcs

phpmd: ## Run PHP Mess Detector
	$(cmd) php vendor/bin/phpmd src text ./phpmd.xml.dist
.PHONY: phpmd

phpcbf: ## Run PHP Code Beautifier & Fixer
	$(cmd) php vendor/bin/phpcbf --extensions=php --standard=PSR1,PSR12 -p config migrations public src tests
.PHONY: phpcbf

phpcsf: ## Run PHP Code Sniffer Fixer
	$(cmd) php vendor/bin/php-cs-fixer fix --config='.php-cs-fixer.dist.php' --verbose
	@printf '\n'
.PHONY: phpcsf

##

report-coverage: ## Create coverage HTML report
	@$(MAKE) --no-print-directory install-database env=test
	$(cmd) php -dxdebug.mode=coverage -dxdebug.profiler_enable=off vendor/bin/phpunit \
		--stop-on-failure \
		--verbose \
		--group=unit,functional \
		--coverage-html=var/cache/phpunit/html
	@printf '\nHTML report generated in "var/cache/phpunit/html" directory\n\n'
.PHONY: report-coverage

report-metrics: ## Create metrics report
	$(cmd) php vendor/bin/phpmetrics --report-html=var/cache/phpmetrics src
.PHONY: report-metrics

##

composer-check: ## Check composer.lock and outdated dependencies
	$(cmd) composer update --lock
	$(cmd) composer outdated -D
.PHONY: composer-check
