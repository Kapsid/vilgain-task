# Vilgain Task - REST API

REST API for User and Article management built with Symfony 7.2.

## Quick Start

```bash
make run
```

This will:
- Start Docker containers
- Install dependencies
- Generate JWT keys
- Create database with example data
- Start the API

**URLs:**
- API: http://localhost:8080/api/v1
- Swagger UI: http://localhost:8080/api/doc

## Make Commands

| Command | Description |
|---------|-------------|
| `make run` | Full setup with fixtures |
| `make up` | Start containers |
| `make down` | Stop and remove containers |
| `make stop` | Stop containers (without removing) |
| `make reset` | Reset database (drop, recreate, migrate, fixtures) |
| `make test` | Run tests |
| `make fixtures` | Reload example data |

## API Endpoints

### Auth
```bash
# Register
POST /api/v1/auth/register
{"email": "user@example.com", "password": "SecurePass123!", "name": "User", "role": "author"}

# Login (returns JWT token)
POST /api/v1/auth/login
{"email": "admin@example.com", "password": "AdminPass123!"}
```

### Articles (public read, author/admin write)
```bash
GET /api/v1/articles
GET /api/v1/articles/{id}
POST /api/v1/articles
PUT /api/v1/articles/{id}
DELETE /api/v1/articles/{id}
```

### Users (admin only)
```bash
GET /api/v1/users
GET /api/v1/users/{id}
POST /api/v1/users
PUT /api/v1/users/{id}
DELETE /api/v1/users/{id}
```

## Example: Create Article (for more check Swagger docs)

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"author@example.com","password":"AuthorPass123!"}' | jq -r '.token')

# 2. Create article
curl -X POST http://localhost:8080/api/v1/articles \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"My Article","content":"This is the content of my article which needs to be at least 10 characters."}'
```

## Roles

| Role | Can do |
|------|--------|
| Admin | Everything |
| Author | Create articles, edit/delete own |
| Reader | View articles only |

## Password Requirements

- Minimum 12 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character (`!@#$%^&*` etc.)

## Tech Stack

- PHP 8.2 / Symfony 7.2
- PostgreSQL 16
- Doctrine ORM
- JWT Authentication
- Swagger/OpenAPI
- PHPStan and PHP-CS-Fixer in pre-commit
- PHPUnit for testing
- Rate limiting (see `config/packages/rate_limiter.yaml`)

## Potential improvements for the future
- Check leaked passwords when registering (NotCompromisedPassword)
- More test coverage - services etc.
- API: Allow to change password
- API: Implementation of sorting and filtering
- JWT refresh token
- CORS configuration for frontend integration
