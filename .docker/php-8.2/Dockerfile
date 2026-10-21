# PHP runtime for Oro 5.1.x (composer.json: "php": "~8.2.0", package.json: node 18)
FROM php:8.2-cli-bookworm

ARG UID=1001
ARG GID=1001

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN apt-get update && apt-get install -y --no-install-recommends git unzip \
    && install-php-extensions pdo_pgsql intl gd zip bcmath soap gmp imap opcache tidy redis sockets exif pcntl \
    && rm -rf /var/lib/apt/lists/*

# Composer + Symfony CLI (same commands as on a host install: symfony console / symfony composer)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY --from=ghcr.io/symfony-cli/symfony-cli:latest /usr/local/bin/symfony /usr/local/bin/symfony

# Node 18 + npm (copied piecewise: copying all of /usr/local would clobber PHP)
COPY --from=node:18-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:18-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s ../lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

# The base image ships no php.ini (built-in defaults, e.g. short_open_tag=On); start from the dev template
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini \
    && { echo 'memory_limit=-1'; echo 'max_execution_time=0'; echo 'short_open_tag=Off'; } > /usr/local/etc/php/conf.d/oro.ini

# Match the host user so vendor/, var/ and node_modules/ stay owned by you
RUN groupadd -g ${GID} oro && useradd -m -u ${UID} -g ${GID} oro
USER oro
WORKDIR /var/www/oro
