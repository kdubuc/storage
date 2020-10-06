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

# Launch Web Action Request Handler using Reactor Pattern.
# Bind HTTP Pipeline to 0.0.0.0 (all interfaces).
CMD php vendor/bin/ppm start --bootstrap="Kdubuc\\Storage\\API" --bridge="Psr15Middleware" --port=80 --host=0.0.0.0 --app-env=prod --debug=0

# Import PHP function into the container workdir.
# Since /vendor is rarely updated compared to all others files, we use
# multi-layered copy, and import it first.
COPY vendor vendor
COPY src src

# HTTP Port
EXPOSE 80

# Shared volume : data 
VOLUME /usr/data