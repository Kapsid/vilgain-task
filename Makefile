.PHONY: up down stop run test fixtures build cs-fix cs-check stan hooks-install test-setup quality setup jwt wait reset

# Initial setup: copy .env.example to .env if needed
setup:
	@if [ ! -f .env ]; then \
		echo "Creating .env from .env.example..."; \
		cp .env.example .env; \
		echo "Done! Edit .env with your settings if needed."; \
	else \
		echo ".env already exists."; \
	fi

# Build containers
build:
	docker-compose build

# Start containers
up: build
	docker-compose up -d

# Stop and remove containers
down:
	docker-compose down

# Stop containers (without removing)
stop:
	docker-compose stop

# Wait for app container to be ready
wait:
	@echo "Waiting for containers to be ready..."
	@until docker-compose exec -T app echo "App ready" 2>/dev/null; do sleep 2; done

# Full setup: start, install deps, generate JWT keys, run migrations, load fixtures
run: setup up wait
	@echo "Installing dependencies..."
	docker-compose exec app composer install --no-interaction
	@echo "Generating JWT keys..."
	docker-compose exec app php bin/console lexik:jwt:generate-keypair --skip-if-exists
	@echo "Creating database..."
	docker-compose exec app php bin/console doctrine:database:create --if-not-exists
	@echo "Running migrations..."
	docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction
	@echo "Loading fixtures..."
	docker-compose exec app php bin/console doctrine:fixtures:load --no-interaction
	@echo ""
	@echo "Setup complete!"
	@echo ""
	@echo "API: http://localhost:8080"
	@echo "Swagger: http://localhost:8080/api/doc"
	@echo ""
	@echo "Test admin account:"
	@echo "  admin@example.com / AdminPass123!"

# Setup test database
test-setup:
	docker-compose exec app php bin/console doctrine:database:create --if-not-exists --env=test
	docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction --env=test

# Run tests
test: test-setup
	docker-compose exec app php bin/phpunit

# Reload fixtures
fixtures:
	docker-compose exec app php bin/console doctrine:fixtures:load --no-interaction

# Reset database (drop and recreate)
reset:
	docker-compose exec app php bin/console doctrine:database:drop --force --if-exists
	docker-compose exec app php bin/console doctrine:database:create
	docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction
	docker-compose exec app php bin/console doctrine:fixtures:load --no-interaction
	@echo "Database reset complete!"

# Fix code style
cs-fix:
	docker-compose exec app vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php

# Check code style (dry-run)
cs-check:
	docker-compose exec app vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff

# Run PHPStan static analysis
stan:
	docker-compose exec app vendor/bin/phpstan analyse --memory-limit=512M

# Install git hooks
hooks-install:
	git config core.hooksPath .githooks
	@echo "Git hooks installed. Pre-commit hook will run PHP CS Fixer and PHPStan on staged files."

# Run all quality checks (CS Fixer check + PHPStan + Tests)
quality: cs-check stan test

# Generate JWT keys
jwt:
	docker-compose exec app php bin/console lexik:jwt:generate-keypair --overwrite
