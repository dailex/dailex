################################################################################
# Base image
################################################################################

FROM php:7.2-fpm

################################################################################
# Build instructions
################################################################################

# Install packages
RUN apt-get update && apt-get install -y \
    libmcrypt-dev \
    libicu-dev \
    libxslt-dev \
    libzmq3-dev \
    libevent-dev
RUN docker-php-ext-install -j$(nproc) \
    intl \
    xsl \
    sockets \
    bcmath \
    pcntl \
    opcache
RUN pecl install ev-1.0.3 zmq-1.1.3 xdebug-2.6.1 mcrypt-1.0.1 \
  && docker-php-ext-enable ev zmq mcrypt
