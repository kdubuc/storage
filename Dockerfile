FROM php:7.4-alpine
    
# Signals.
RUN docker-php-ext-install pcntl

# Curl.
RUN apk --no-cache add curl

# Healthcheck routine.
HEALTHCHECK --interval=3s --timeout=3s CMD curl -sS --fail --head 0.0.0.0:80/_healthcheck || exit 1

# Boot upload max filesize
COPY docker/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Work folder.
WORKDIR /usr/src

# Install dependencies (prod mode)
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install -n --no-dev --no-suggest --no-progress --no-scripts --ignore-platform-reqs 

# Import PHP sources into the container workdir.
COPY src src

# Launch Web Action Request Handler using Reactor Pattern.
# Bind HTTP Pipeline to 0.0.0.0 (all interfaces).
CMD php vendor/bin/ppm start --bootstrap="Kdubuc\\Storage\\API" --bridge="Psr15Middleware" --port=80 --host=0.0.0.0 --app-env=prod --debug=0

# HTTP Port
EXPOSE 80

# Shared volume : data 
VOLUME /usr/data