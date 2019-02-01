FROM php:7.2.7-cli-alpine

LABEL maintainer="FOLGER" \
      description="FOLGER AssetsRetrieval App"

RUN apk add --update --no-cache libxml2-dev php7-pcntl && \
    docker-php-ext-configure pcntl --enable-pcntl && \
    docker-php-ext-install xml opcache pcntl

WORKDIR /app

ARG SYMFONY_ENV=dev

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_CACHE_DIR=/dev/null
ENV SYMFONY_ENV=$SYMFONY_ENV

COPY composer.json composer.lock[t] symfony.lock[t] /app/
COPY cache /app/cache
COPY bin /app/bin

RUN php -r "readfile('https://getcomposer.org/installer');" | php && \
    if [ "$SYMFONY_ENV" == "dev" ] ; then php composer.phar install --optimize-autoloader; else php composer.phar install --optimize-autoloader --no-dev ; fi && \
    rm -rf ~/.composer composer.phar

COPY config /app/config
COPY src /app/src

CMD ["bin/console"]
