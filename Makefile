##
# Variables.
#
# ROOT       Set this to 1 to run the command as root.
##
ROOT ?= 0
DEBUG ?= 0
ARGS ?=
VERSION ?= qit_dev_build

# List all PHP versions you want to test/build images for:
PHP_VERSIONS = 7.4 8.0 8.1 8.2 8.3 8.4
# Default PHP version used if none specified
PHP_VERSION ?= 8.3

ifeq (1, $(ROOT))
DOCKER_USER ?= "0:0"
else
DOCKER_USER ?= "$(shell id -u):$(shell id -g)"
endif

ifeq ($(DEBUG),1)
XDEBUG_FLAGS = -d xdebug.mode=debug -d xdebug.start_with_request=yes
else
XDEBUG_FLAGS = -d xdebug.mode=off
endif


## Run a command inside a PHP CLI Docker image built for a specific PHP_VERSION.
## 1. Command to execute, e.g.: "php /app/src/vendor/bin/phpcs"
## 2. Working dir (optional)
define execPhpAlpine
	@docker image inspect qit-cli-tests:$(PHP_VERSION) > /dev/null 2>&1 || \
	(echo "Docker image not found. Building qit-cli-tests:$(PHP_VERSION)..." && \
	 docker build --build-arg CI=${CI} --build-arg PHP_VERSION=$(PHP_VERSION) -t qit-cli-tests:$(PHP_VERSION) ./_build/docker/php)

	@mkdir -p /tmp/qit
	@chmod 777 /tmp/qit # Ensure writable by container user

	@docker run --rm \
		--user $(DOCKER_USER) \
		-v "${PWD}:/app" \
		-v "/tmp/qit:/tmp/qit" \
		-v "${PWD}/_build/docker/php/ini/xdebug.ini:/usr/local/etc/php/conf.d/xdebug.ini" \
		--env QIT_HOME=/tmp \
		--env PHP_IDE_CONFIG=serverName=qit_cli \
		--env TARGET_PHP_VERSION=$(PHP_VERSION) \
		--env PHAN_DISABLE_XDEBUG_WARN=1 \
		--env PHAN_ALLOW_XDEBUG=1 \
		--workdir "$(2:=/)" \
		--add-host=host.docker.internal:host-gateway \
		qit-cli-tests:$(PHP_VERSION) \
		bash -c "php $(XDEBUG_FLAGS) $(1)"
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

	@sed "s/QIT_VERSION_REPLACE/$(VERSION)/g" ./_build/box.json.dist > ./_build/box.json

	@docker images -q | grep qit-cli-box || docker build -t qit-cli-box ./_build/docker/box
	@docker run --rm -v ${PWD}:${PWD} -w ${PWD} -u "$(shell id -u):$(shell id -g)" qit-cli-box ./_build/box.phar compile -c ./_build/box.json --no-parallel || rm -rf src-tmp

	@rm -rf src-tmp
	@rm -f ./_build/box.json

.PHONY: tests
tests:
	$(MAKE) phpcs
	$(MAKE) phpstan
	$(MAKE) phpunit
	$(MAKE) phan

tests-all:
	$(MAKE) phpcs
	$(MAKE) phpstan
	$(MAKE) phpunit-all
	$(MAKE) phan-all

phpcbf:
	$(call execPhpAlpine,/app/src/vendor/bin/phpcbf /app/src/qit-cli.php /app/src/src -s --standard=/app/src/.phpcs.xml.dist)

phpcs:
	$(MAKE) phpcbf || true
	$(call execPhpAlpine,/app/src/vendor/bin/phpcs /app/src/qit-cli.php /app/src/src -s --standard=/app/src/.phpcs.xml.dist)

# Run PHPStan without baseline
phpstan:
	@echo "Running PHPStan..."
	$(call execPhpAlpine,/app/src/vendor/bin/phpstan -vvv analyse -c /app/src/phpstan.neon --memory-limit=1G)

phpunit:
	$(call execPhpAlpine,/app/src/vendor/bin/phpunit -c /app/src/phpunit.xml.dist $(ARGS),/app/src)

phpunit-all:
	@for ver in $(PHP_VERSIONS); do \
		echo "Running PHPUnit on PHP $$ver..."; \
		$(MAKE) phpunit PHP_VERSION=$$ver || exit 1; \
	done

phan-all:
	@for ver in $(PHP_VERSIONS); do \
		echo "Running Phan on PHP $$ver..."; \
		$(MAKE) phan PHP_VERSION=$$ver || exit 1; \
	done

check-php-versions:
	@for ver in $(PHP_VERSIONS); do \
		echo "Checking PHP version in qit-cli-tests:$$ver..."; \
		docker image inspect qit-cli-tests:$$ver >/dev/null 2>&1 || (echo "Image not found for $$ver, building..." && docker build --build-arg CI=${CI} --build-arg PHP_VERSION=$$ver -t qit-cli-tests:$$ver ./_build/docker/php); \
		docker run --rm qit-cli-tests:$$ver php -v; \
		echo "-----------------------------------"; \
	done

rebuild-images:
	@for ver in $(PHP_VERSIONS); do \
		echo "Rebuilding qit-cli-tests:$$ver..."; \
		docker build --no-cache --build-arg CI=${CI} --build-arg PHP_VERSION=$$ver -t qit-cli-tests:$$ver ./_build/docker/php || exit 1; \
	done

phan:
	$(call execPhpAlpine,/app/src/vendor/bin/phan $(ARGS),/app/src)
