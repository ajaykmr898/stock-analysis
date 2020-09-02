FROM httpd:2.4-alpine

RUN apk update; \
    apk upgrade;

# Copy apache vhost file to proxy php requests to php-fpm container
COPY sites /usr/local/apache2/conf

# Include default and custom configurations
RUN echo "Include /usr/local/apache2/conf/000-common.conf" >> /usr/local/apache2/conf/httpd.conf
