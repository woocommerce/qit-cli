##
# Variables.
#
# ROOT       Set this to 1 to run the command as root.
##
ROOT ?= 0
DEBUG ?= 0
ARGS ?=
VERSION ?= qit_dev_build

ifeq (1, $(ROOT))
DOCKER_USER ?= "0:0"
else
DOCKER_USER ?= "$(shell id -u):$(shell id -g)"
endif

## Run a command inside an alpine PHP 8 CLI image.
## 1. Command to execute, eg: "./vendor/bin/phpcs" 2. Working dir (optional)
define execPhpAlpine
    @docker image inspect qit-cli-php-xdebug-pcntl > /dev/null 2>&1 || docker build --build-arg CI=${CI} -t qit-cli-php-xdebug-pcntl ./_build/docker/php83
    docker run --rm \
        --user $(DOCKER_USER) \
        -v "${PWD}:/app" \
        -v "${PWD}/_build/docker/php83/ini/xdebug.ini:/usr/local/etc/php/conf.d/xdebug.ini" \
        --env QIT_HOME=/tmp \
        --env PHP_IDE_CONFIG=serverName=qit_cli \
        --workdir "$(2:=/)" \
        --add-host host.docker.internal:host-gateway \
        qit-cli-php-xdebug-pcntl \
        bash -c "php -d xdebug.start_with_request=$(if $(filter 1,$(DEBUG)),yes,no) -d memory_limit=1G $(1)"
endef

watch:
	@cd _build; php watch.php

# Build the Phar file of the CD Client.
build:
	@cp -r src src-tmp
	@COMPOSER_HOME_DIR=$$(composer config --global home); \
		docker run --rm \
			--volume ${PWD}/src-tmp:/app \
			--volume $${COMPOSER_HOME_DIR}:/tmp \
			--user "$(shell id -u):$(shell id -g)" \
			composer \
			install --no-dev --quiet --optimize-autoloader --ignore-platform-reqs

	# Create a temporary configuration file with the specified VERSION
	@sed "s/QIT_VERSION_REPLACE/$(VERSION)/g" ./_build/box.json.dist > ./_build/box.json

	# Ensure the Docker image is built and run Box with the temporary configuration file
	@docker images -q | grep qit-cli-box || docker build -t qit-cli-box ./_build/docker/box
	@docker run --rm -v ${PWD}:${PWD} -w ${PWD} -u "$(shell id -u):$(shell id -g)" qit-cli-box ./_build/box.phar compile -c ./_build/box.json --no-parallel || rm -rf src-tmp

	# Clean up the temporary directory and configuration file
	@rm -rf src-tmp
	@rm -f ./_build/box.json

tests:
	$(MAKE) phpcs
	$(MAKE) phpstan
	$(MAKE) phpunit
	$(MAKE) phan

phpcbf:
	$(call execPhpAlpine,/app/src/vendor/bin/phpcbf /app/src/qit-cli.php /app/src/src -s --standard=/app/src/.phpcs.xml.dist)

phpcs:
	$(MAKE) phpcbf || true
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