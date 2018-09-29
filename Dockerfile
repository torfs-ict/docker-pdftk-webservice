FROM php:7.2-cli-alpine

# Container configuration
EXPOSE 80
ENTRYPOINT php /app/bin/console server:RUN 0.0.0.0:80

# Install pdftk
RUN apk update && apk upgrade \
&& apk add pdftk

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install the application
ENV APP_ENV prod
COPY . /app
WORKDIR /app
RUN composer install