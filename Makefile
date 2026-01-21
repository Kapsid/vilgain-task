.PHONY: up down run test fixtures build cs-fix cs-check hooks-install test-setup

# Build containers
build:
	docker-compose build

# Start containers
up: build
	docker-compose up -d

# Stop containers
down:
	docker-compose down

# Full setup: start, install deps, generate JWT keys, setup DB with fixtures
run: up
	@sleep 3
	docker-compose exec app composer install --no-interaction
	docker-compose exec app php bin/console lexik:jwt:generate-keypair --skip-if-exists
	docker-compose exec app php bin/console doctrine:database:create --if-not-exists
	docker-compose exec app php bin/console doctrine:schema:update --force
	docker-compose exec app php bin/console doctrine:fixtures:load --no-interaction
	@echo ""
	@echo "API: http://localhost:8080"
	@echo "Swagger: http://localhost:8080/api/doc"
	@echo ""
	@echo "Test users:"
	@echo "  admin@example.com / admin123"
	@echo "  author@example.com / author123"
	@echo "  reader@example.com / reader123"

# Setup test database
test-setup:
	docker-compose exec app php bin/console doctrine:database:create --if-not-exists --env=test
	docker-compose exec app php bin/console doctrine:schema:update --force --env=test

# Run tests
test: test-setup
	docker-compose exec app php bin/phpunit

# Reload fixtures
fixtures:
	docker-compose exec app php bin/console doctrine:fixtures:load --no-interaction

# Fix code style
cs-fix:
	docker-compose exec app vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php

# Check code style (dry-run)
cs-check:
	docker-compose exec app vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff

# Install git hooks
hooks-install:
	git config core.hooksPath .githooks
	@echo "Git hooks installed. Pre-commit hook will run PHP CS Fixer on staged files."
