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
- API: http://localhost:8080
- Swagger UI: http://localhost:8080/api/doc

## Make Commands

| Command | Description |
|---------|-------------|
| `make run` | Full setup with fixtures |
| `make up` | Start containers |
| `make down` | Stop containers |
| `make test` | Run tests |
| `make fixtures` | Reload example data |

## API Endpoints

### Auth
```bash
# Register
POST /auth/register
{"email": "user@example.com", "password": "pass123", "name": "User", "role": "author"}

# Login (returns JWT token)
POST /auth/login
{"email": "admin@example.com", "password": "admin123"}
```

### Articles (public read, author/admin write)
```bash
GET /articles              # List all
GET /articles/{id}         # Get one
POST /articles             # Create (author/admin)
PUT /articles/{id}         # Update (owner/admin)
DELETE /articles/{id}      # Delete (owner/admin)
```

### Users (admin only)
```bash
GET /users
GET /users/{id}
POST /users
PUT /users/{id}
DELETE /users/{id}
```

## Example: Create Article

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"author@example.com","password":"author123"}' | jq -r '.token')

# 2. Create article
curl -X POST http://localhost:8080/articles \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"My Article","content":"Content here"}'
```

## Roles

| Role | Can do |
|------|--------|
| Admin | Everything |
| Author | Create articles, edit/delete own |
| Reader | View articles only |

## Tech Stack

- PHP 8.2 / Symfony 7.2
- PostgreSQL 16
- Doctrine ORM
- JWT Authentication
- Swagger/OpenAPI
