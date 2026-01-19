FROM php:8.2-cli

WORKDIR /app

# Install system dependencies
# Added postgresql-client for pg_isready or manual DB checks if needed
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libpq-dev zip postgresql-client \
    && docker-php-ext-install intl pdo pdo_pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --optimize-autoloader --no-scripts
RUN composer dump-autoload --optimize --classmap-authoritative


# Create empty .env file to prevent Symfony Dotenv exception
RUN touch .env

# Install assets and importmap dependencies
# We need a dummy DATABASE_URL because the kernel boots during these commands
ENV DATABASE_URL="postgresql://build:build@127.0.0.1:5432/build"
RUN php bin/console assets:install public
RUN php bin/console importmap:install



ENV APP_ENV=dev
ENV TRUSTED_PROXIES=*

# Updated CMD: Attempt migration, but don't crash immediately if it fails (retry logic or just proceed)
# For now, we use a simple approach: Try migrate, if fail, echo error but start server anyway. 
# This prevents the "CrashLoopBackOff" if DB is temporarily slow.
CMD sh -c "php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || echo 'Migration failed, starting anyway...' && php -S 0.0.0.0:${PORT:-80} -t public"
