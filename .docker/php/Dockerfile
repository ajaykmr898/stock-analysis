ARG PHP_VERSION="7.3"

FROM php:${PHP_VERSION}-fpm

# Install the PHP extention using "apt"
RUN apt-get update && \
  apt-get install -y --no-install-recommends \
    apt-utils \
    curl \
    libmemcached-dev \
    libzip-dev \
    libz-dev \
    libpq-dev \
    libjpeg-dev \
    libmagickwand-dev \
    libmagickcore-dev \
    libpng-dev \
    libfreetype6-dev \
    libssl-dev \
    libmcrypt-dev \
    mcrypt \
    zlib1g-dev \
    libicu-dev \
    libpq-dev \
    libssl-dev \
    gettext \
    g++ \
  && apt-get autoremove -y \
  && apt-get clean

# Install the PHP extention using "docker-php"
RUN docker-php-ext-install gettext \
  && docker-php-ext-configure intl \
  && docker-php-ext-install intl \
  && docker-php-ext-install zip \
  && docker-php-ext-install pdo_mysql \
  && docker-php-ext-install exif \
  && docker-php-ext-enable exif \
  && docker-php-ext-configure gd \
    --with-jpeg-dir=/usr/lib/ \
    --with-freetype-dir=/usr/include/ \
  && docker-php-ext-install gd \
  && rm -rf /var/lib/apt/lists/*

COPY configuration/php.ini /usr/local/etc/php/php.ini

# Install mcrypt in PHP > 7.1 (deprecated)
RUN apt-get -y install libmcrypt-dev && \
  pecl install mcrypt-1.0.2 && \
  docker-php-ext-enable mcrypt

# Install mongodb extention using "pecl" and "docker-php"
RUN pecl install mongodb \
  && docker-php-ext-enable mongodb

# Install redis extention using "pecl" and "docker-php"
RUN pecl install -o -f redis \
  &&  rm -rf /tmp/pear \
  &&  docker-php-ext-enable redis

# Install 'xdebug-2.5.5' for PHP 7
RUN pecl install xdebug \
  && docker-php-ext-enable xdebug

# Install 'imagick' for PHP 7
RUN pecl install imagick \
  && docker-php-ext-enable imagick

# Install 'Java jre 8'
RUN mkdir -p /usr/lib/jvm
ADD https://pardgroup-files.s3-eu-west-1.amazonaws.com/jre-8u241-linux-x64.tar.gz /usr/lib/jvm
RUN tar zxvf /usr/lib/jvm/jre-8u241-linux-x64.tar.gz -C /usr/lib/jvm \
  && rm -f /usr/lib/jvm/jre-8u241-linux-x64.tar.gchz \
  && update-alternatives --install "/usr/bin/java" "java" "/usr/lib/jvm/jre1.8.0_241/bin/java" 1 \
  && update-alternatives --config java

# Install composer and add its bin to the PATH.
RUN echo "export JAVA_HOME=/usr/bin/java" >> ~/.bashrc

RUN echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" >> /usr/local/etc/php/php.ini

COPY configuration/php.ini /etc/php/7.3/cli/conf.d/stock.ini
