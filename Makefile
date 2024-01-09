##
# Variables.
#
# ROOT       Set this to 1 to run the command as root.
##
ROOT ?= 0
ARGS ?=

ifeq (1, $(ROOT))
DOCKER_USER ?= "0:0"
else
DOCKER_USER ?= "$(shell id -u):$(shell id -g)"
endif

## Run a command inside an alpine PHP 7 CLI image.
## 1. Command to execute, eg: "./vendor/bin/phpcs" 2. Working dir (optional)
define execPhpAlpine
	@docker images -q | grep qit-cli-php || docker build -t qit-cli-php ./_build/docker/php74
	docker run --rm \
		--user $(DOCKER_USER) \
		-v "${PWD}:/app" \
		--env QIT_HOME=/tmp \
		--workdir "$(2:=/)" \
		qit-cli-php \
		bash -c "php -d memory_limit=1G $(1)"
endef

watch:
	@cd _build; php watch.php

# Build the Phar file of the CD Client.
build:
	@cp -r src src-tmp
	@docker run --rm \
			--volume ${PWD}/src-tmp:/app \
			--volume ${HOME}/.composer}:/tmp \
			--user "$(shell id -u):$(shell id -g)" \
			composer \
			install --no-dev --quiet --optimize-autoloader --ignore-platform-reqs
	@docker images -q | grep qit-cli-box || docker build -t qit-cli-box ./_build/docker/box
	@docker run --rm -v ${PWD}:${PWD} -w ${PWD} -u "$(shell id -u):$(shell id -g)" qit-cli-box ./_build/box.phar compile -c ./_build/box.json.dist --no-parallel || rm -rf src-tmp
	@rm -rf src-tmp

tests:
	$(MAKE) phpcs
	$(MAKE) phpstan
	$(MAKE) phpunit
	$(MAKE) phan

phpcbf:
	$(call execPhpAlpine,/app/src/vendor/bin/phpcbf /app/src/qit-cli.php /app/src/src -s --standard=/app/src/.phpcs.xml.dist)

phpcs:
	$(call execPhpAlpine,/app/src/vendor/bin/phpcs /app/src/qit-cli.php /app/src/src -s --standard=/app/src/.phpcs.xml.dist)

phpstan:
	$(call execPhpAlpine,/app/src/vendor/bin/phpstan -vvv analyse -c /app/src/phpstan.neon)

phpunit:
	$(call execPhpAlpine,/app/src/vendor/bin/phpunit -c /app/src/phpunit.xml.dist $(ARGS))

phan:
	docker run --rm \
		-v ${PWD}/src:/mnt/src \
		-u "$$(id -u):$$(id -g)" \
		phanphp/phan:latest $(ARGS)
	# PS: To update Phan, run: docker image pull phanphp/phan:latest