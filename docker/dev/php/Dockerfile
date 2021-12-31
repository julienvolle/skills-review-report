FROM php:7.4-fpm-alpine

# create php.ini
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

# install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# install nodejs + npm + yarn
RUN apk update \
    && apk add nodejs \
    && apk add npm \
    && npm install --global -y yarn

# install git
RUN apk add git \
    && git config --global --add safe.directory /var/www/html

# install mysql driver
RUN docker-php-ext-install pdo pdo_mysql

# install intl
RUN apk add icu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && echo "intl.default_locale = en" >> /usr/local/etc/php/conf.d/docker-php-ext-intl.ini

# install opcache
RUN docker-php-ext-install opcache

# install apcu
RUN set -xe \
    && apk add --no-cache --update --virtual .phpize-deps $PHPIZE_DEPS \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && pecl clear-cache

# install xdebug (<=3.1.5 with PHP 7.4)
RUN set -xe \
    && pecl install xdebug-3.1.5 \
    && docker-php-ext-enable xdebug \
    && pecl clear-cache

# custom profile
RUN echo 'alias ll="ls -al"' >> ~/.profile
RUN echo "parse_git_branch() {" >> ~/.profile \
    && echo "    git branch 2> /dev/null | sed -e '/^[^*]/d' -e 's/* \(.*\)/ (\1)/'" >> ~/.profile \
    && echo "}" >> ~/.profile \
    && echo "export PS1='\u@\h \[\033[32m\]\w\[\033[33m\]\$(parse_git_branch)\[\033[00m\] $ '" >> ~/.profile
