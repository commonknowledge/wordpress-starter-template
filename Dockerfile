FROM php:8.3.12-fpm-alpine

ENV PATH "$PATH:/var/www/html/vendor/bin"

ENV PHP_GD_DEPS "freetype-dev libjpeg-turbo-dev libpng-dev libzip libzip-dev"

# Set up PHP with modules and ini settings for running WordPress
RUN apk update \
    && apk add --no-cache $PHP_GD_DEPS \
    && docker-php-ext-install gd mysqli pdo pdo_mysql zip \
    && echo "date.timezone=Europe/London" > /usr/local/etc/php/conf.d/zz-custom.ini \
    && echo "session.autostart=0" >> /usr/local/etc/php/conf.d/zz-custom.ini

# TODO: replace `git clone imagick && pecl install /tmp/imagick/package.xml` with `pecl install imagick`
# when this issue is fixed on their pecl release: https://github.com/Imagick/imagick/issues/640
RUN apk update && apk add --virtual --no-cache \
    imagemagick imagemagick-dev linux-headers git $PHPIZE_DEPS \
    && git clone https://github.com/Imagick/imagick.git /tmp/imagick \
    && pecl install /tmp/imagick/package.xml \
    && pecl install xdebug \
    && docker-php-ext-enable imagick xdebug \
    && apk del imagemagick-dev linux-headers $PHPIZE_DEPS \
    && rm -rf /tmp/imagick

RUN { \
    echo "zend_extension=xdebug"; \
    echo "xdebug.mode=develop,debug"; \
    echo "xdebug.start_with_request=yes"; \
    echo "xdebug.client_host=host.docker.internal"; \
    echo "xdebug.idekey=VSCODE"; \
    echo "xdebug.log=/tmp/xdebug_remote.log"; \
} > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini;

# Install wp-cli
RUN curl -o /bin/wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN chmod +x /bin/wp-cli.phar
RUN cd /bin && mv wp-cli.phar wp
