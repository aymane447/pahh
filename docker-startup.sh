#!/bin/sh
echo "Deployment starting..."

# Retry migrations up to 5 times (to handle DB startup delay)
MAX_RETRIES=5
COUNT=0
SUCCESS=0

while [ $COUNT -lt $MAX_RETRIES ]; do
    echo "Attempting migration (Try $((COUNT+1))/$MAX_RETRIES)..."
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
    if [ $? -eq 0 ]; then
        echo "Migration successful!"
        SUCCESS=1
        break
    fi
    echo "Migration failed. Retrying in 5 seconds..."
    sleep 5
    COUNT=$((COUNT+1))
done

if [ $SUCCESS -eq 0 ]; then
    echo "WARNING: Migrations failed after $MAX_RETRIES attempts. Starting app anyway (tables may be missing)..."
fi

echo "Starting PHP server..."
php -S 0.0.0.0:${PORT:-80} -t public
