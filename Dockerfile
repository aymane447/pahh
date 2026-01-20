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
# Install assets and importmap dependencies
# We need dummy env vars because the kernel boots during these commands
ENV DATABASE_URL="postgresql://build:build@127.0.0.1:5432/build"
ENV DEFAULT_URI="http://localhost"
RUN php bin/console importmap:install
RUN php bin/console assets:install public



# Copy startup script
COPY docker-startup.sh /usr/local/bin/docker-startup
RUN chmod +x /usr/local/bin/docker-startup

ENV APP_ENV=prod

ENV TRUSTED_PROXIES=*

CMD ["/usr/local/bin/docker-startup"]
