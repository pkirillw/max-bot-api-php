.PHONY: help install test lint phpstan shell

PHP_CONTAINER := php
COMPOSE := docker compose run --rm $(PHP_CONTAINER)

help:
	@echo "Available targets:"
	@echo "  install  — composer install (no dev-skip)"
	@echo "  test     — run phpunit"
	@echo "  lint     — php -l on every src file"
	@echo "  phpstan  — static analysis (level 6)"
	@echo "  shell    — bash inside the PHP container"

install:
	$(COMPOSE) composer install --no-interaction --prefer-dist

test:
	$(COMPOSE) vendor/bin/phpunit --colors=always

lint:
	$(COMPOSE) bash -c 'find src tests -name "*.php" -exec php -l {} \; 2>&1 | grep -v "No syntax errors" || true'

phpstan:
	$(COMPOSE) vendor/bin/phpstan analyse src level 6 --no-progress

shell:
	$(COMPOSE) bash
