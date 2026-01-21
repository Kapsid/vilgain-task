#!/bin/bash
set -e

# Copy .env.example to .env if .env doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

echo "Waiting for database to be ready..."

# Wait for database to be available
until PGPASSWORD=$POSTGRES_PASSWORD psql -h "$DB_HOST" -U "$POSTGRES_USER" -d "$POSTGRES_DB" -c '\q' 2>/dev/null; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "Database is ready!"
echo "Container started. Run 'make run' for full setup or use docker-compose exec for manual commands."

# Execute the main container command
exec "$@"
