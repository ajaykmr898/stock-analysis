FROM phusion/baseimage:latest

RUN DEBIAN_FRONTEND=noninteractive
RUN locale-gen en_US.UTF-8

ENV LANGUAGE=en_US.UTF-8
ENV LC_ALL=en_US.UTF-8
ENV LC_CTYPE=en_US.UTF-8
ENV LANG=en_US.UTF-8
ENV TERM xterm

# Add the PHP ppa
RUN apt-get install -y software-properties-common && \
    add-apt-repository -y ppa:ondrej/php

# Install "PHP Extentions", "libraries", "Software's"
RUN apt-get update && \
    apt-get install -y --allow-downgrades --allow-remove-essential \
        --allow-change-held-packages \
        php7.3-cli \
        php7.3-common \
        php7.3-curl \
        php7.3-intl \
        php7.3-json \
        php7.3-xml \
        php7.3-mbstring \
        mcrypt \
        php7.3-mysql \
        php7.3-zip \
        php7.3-memcached \
        php7.3-cgi \
        php7.3-gd \
        php7.3-dev \
        pkg-config \
        php7.3-imagick \
        imagemagick \
        libmagickwand-dev \
        libmagickcore-dev \
        curl \
        vim \
        apt-utils \
        zip unzip && \
        php -m | grep -q 'zip' \
    && apt-get autoremove -y \
    && apt-get clean

RUN pecl install imagick

# Install composer and add its bin to the PATH.
RUN curl -s http://getcomposer.org/installer | php && \
    echo "export PATH=${PATH}:/var/www/vendor/bin" >> ~/.bashrc && \
    mv composer.phar /usr/local/bin/composer

# Install mcrypt for dependencies
RUN apt-get -y install libmcrypt-dev && \
  pecl install mcrypt-1.0.2

#COPY ../php/configuration/php.ini /etc/php/7.3/cli/conf.d/stock.ini

# source env variable
RUN echo ". /etc/profile.d/my_env.sh" >> ~/.bashrc
RUN echo "export STOCK_ENV=local" >> /etc/profile.d/my_env.sh

# Source the bash
RUN . ~/.bashrc

# Working dir
WORKDIR /var/www/html/stock-analysis
