# Base image with PHP and nginx
FROM webdevops/php-nginx:8.3-alpine

ARG VERSION=latest
ENV APP_VERSION=${VERSION} \
    WEB_DOCUMENT_ROOT=/app/public

LABEL org.opencontainers.image.title="Survey" \
    org.opencontainers.image.description="Survey creation tool" \
    org.opencontainers.image.url="https://github.com/barthjs/survey" \
    org.opencontainers.image.source="https://github.com/barthjs/survey" \
    org.opencontainers.image.version=${VERSION} \
    org.opencontainers.image.licenses="MIT"

# Install build dependencies
RUN apk add --no-cache sqlite-dev nodejs npm

# App setup
USER application
WORKDIR /app
COPY --chown=application:application . /app
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && npm install \
    && npm run build \
    && php artisan storage:link \
    && php artisan optimize

USER root

RUN docker-cronjob '* * * * * application php /app/artisan schedule:run >> /dev/null 2>&1'

# Set worker configuration file and container entrypoint script
RUN mv /app/.docker/worker.conf /opt/docker/etc/supervisor.d/worker.conf \
    && mv /app/.docker/start.sh /opt/docker/provision/entrypoint.d/start.sh

# Clean up unnecessary files
RUN rm -rf .docker node_modules resources/css resources/js composer.lock package.json package-lock.json *.js

# Remove build dependencies
RUN apk del sqlite-dev nodejs npm

# Healthcheck configuration
HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -fsS http://127.0.0.1/up || exit 1
