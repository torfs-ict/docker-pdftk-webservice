FROM richarvey/nginx-php-fpm

# Install pdftk
RUN apk update && apk upgrade \
&& apk add pdftk

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install the application
ENV APP_ENV prod
ENV WEBROOT=/pdftk-service/public
ADD docker/nginx.conf /etc/nginx/sites-available/default.conf
COPY . /pdftk-service
WORKDIR /pdftk-service
RUN composer install