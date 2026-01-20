#!/bin/sh
echo "Deployment starting..."

# Retry migrations up to 5 times (to handle DB startup delay)
MAX_RETRIES=5
COUNT=0
SUCCESS=0

while [ $COUNT -lt $MAX_RETRIES ]; do
    echo "Attempting schema update (Try $((COUNT+1))/$MAX_RETRIES)..."
    # Use schema:update to force the database to match the entities (fixes missing tables)
    php bin/console doctrine:schema:update --force --complete
    if [ $? -eq 0 ]; then
        echo "Schema update successful!"
        
        # Seed the database
        echo "Seeding database..."
        php bin/console app:seed-database
        
        # Auto-promote 'ayman' to Admin (as requested)
        echo "Promoting ayman to Admin..."
        php bin/console app:promote-user ayman || true
        
        SUCCESS=1
        break
    fi
    echo "Schema update failed. Retrying in 5 seconds..."
    sleep 5
    COUNT=$((COUNT+1))
done

if [ $SUCCESS -eq 0 ]; then
    echo "WARNING: Migrations failed after $MAX_RETRIES attempts. Starting app anyway (tables may be missing)..."
fi

echo "Starting PHP server..."
php -S 0.0.0.0:${PORT:-80} -t public
