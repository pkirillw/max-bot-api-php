.PHONY: help install test lint phpstan psalm cs-fix cs-check shell

PHP_CONTAINER := php
COMPOSE := docker compose run --rm $(PHP_CONTAINER)

help:
	@echo "Available targets:"
	@echo "  install  — composer install (no dev-skip)"
	@echo "  test     — run phpunit"
	@echo "  lint     — php -l on every src file"
	@echo "  phpstan  — static analysis (level 6)"
	@echo "  psalm    — static analysis (level 5)"
	@echo "  cs-check — check style with php-cs-fixer (dry-run)"
	@echo "  cs-fix   — fix style with php-cs-fixer"
	@echo "  shell    — bash inside the PHP container"

install:
	$(COMPOSE) composer install --no-interaction --prefer-dist

test:
	$(COMPOSE) vendor/bin/phpunit --colors=always

lint:
	$(COMPOSE) bash -c 'find src tests -name "*.php" -exec php -l {} \; 2>&1 | grep -v "No syntax errors" || true'

phpstan:
	$(COMPOSE) vendor/bin/phpstan analyse --no-progress --memory-limit=1G

psalm:
	$(COMPOSE) vendor/bin/psalm --no-progress

cs-check:
	$(COMPOSE) vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix:
	$(COMPOSE) vendor/bin/php-cs-fixer fix

shell:
	$(COMPOSE) bash
