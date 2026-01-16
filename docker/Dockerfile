FROM php:8.4-fpm
LABEL version="0.1.0" \
      license="BSD3" \
      maintainer="Nils Vogels <n.vogels@aves-it.nl>" \
      description="Filesender and simplesamlphp based on php7-fpm" \
      documentation="https://docs.filesender.org"

ENV FILESENDER_VERSION=3.3 \
    SSP_VERSION=2.4.4 \
    FILESENDER_SITE_URL="http://localhost" \
    FILESENDER_ADMIN="root@localhost.localdomain" \
    FILESENDER_ADMIN_EMAIL="root@localhost.localdomain" \
    FILESENDER_EMAIL_REPLY_TO="" \
    FILESENDER_DB_TYPE="mysql" \
    FILESENDER_DB_HOST="localhost" \
    FILESENDER_DB_DATABASE="filesender" \
    FILESENDER_DB_USERNAME="filesender" \
    FILESENDER_DB_PASSWORD="" \
    FILESENDER_DB_USERNAME_ADMIN="filesenderadmin" \
    FILESENDER_DB_PASSWORD_ADMIN="" \
    FILESENDER_AUTH_SP_SAML_SIMPLESAMLPHP_URL="http://localhost/simplesamlphp/" \
    FILESENDER_DEFAULT_TIMEZONE="Europe/Stockholm"

RUN apt-get update && \
    apt-get dist-upgrade -y && \
    apt-get install -y --no-install-recommends \
        nginx \
        wget \
        nano \
        runit && \
    apt-get autoremove -y && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN cd /opt && mkdir filesender && \
    cd /opt/filesender && \
    wget -O filesender.tar.gz https://github.com/filesender/filesender/archive/refs/tags/${FILESENDER_VERSION}.tar.gz && \
    tar xzf filesender.tar.gz && \
    mv filesender-${FILESENDER_VERSION} filesender

RUN cd /opt/filesender && \
    wget -O simplesamlphp.tar.gz https://github.com/simplesamlphp/simplesamlphp/releases/download/v${SSP_VERSION}/simplesamlphp-${SSP_VERSION}-slim.tar.gz && \
    tar xzf simplesamlphp.tar.gz && \
    mv simplesamlphp-${SSP_VERSION} simplesamlphp

ADD config.php /opt/filesender/filesender/config/config.php

RUN cd /opt/filesender/filesender && \
    # cp config/config_sample.php config/config.php && \
    mkdir -p tmp files log /var/cache/simplesamlphp/core /var/cache/simplesamlphp/admin && \
    chmod o-rwx tmp files log config/config.php && \
    chown www-data:www-data tmp files log /var/cache/simplesamlphp/core /var/cache/simplesamlphp/admin && \
    chgrp www-data config/config.php && \
    cd /opt/filesender/simplesamlphp

RUN mkdir -p /config/fpm /config/filesender /config/simplesamlphp/config /config/simplesamlphp/metadata && \
    mv -f /usr/local/etc/php-fpm.d/www.conf /config/fpm/www.conf && \
    mv -f /opt/filesender/filesender/config/config.php /config/filesender/config.php && \
    mv -f /opt/filesender/simplesamlphp/config/*.php.dist /config/simplesamlphp/config/ && \
    mv -f /opt/filesender/simplesamlphp/metadata/*.php.dist /config/simplesamlphp/metadata/ && \
    ln -s /config/fpm/www.conf /usr/local/etc/php-fpm.d/filesender.conf && \
    ln -s /config/filesender/config.php /opt/filesender/filesender/config/config.php && \
    ln -s /config/simplesamlphp/config/acl.php.dist /opt/filesender/simplesamlphp/config/acl.php && \
    ln -s /config/simplesamlphp/config/authsources.php.dist /opt/filesender/simplesamlphp/config/authsources.php && \
    ln -s /config/simplesamlphp/config/config.php.dist /opt/filesender/simplesamlphp/config/config.php && \
    ln -s /config/simplesamlphp/metadata/active.php.dist /opt/filesender/simplesamlphp/metadata/active.php

RUN sed -i -e 's/123/456/g' /config/simplesamlphp/config/config.php.dist
RUN sed -i -e "s/'exampleauth' => false,/'exampleauth' => true,/g" /config/simplesamlphp/config/config.php.dist

ADD assets/usr /usr
ADD assets/etc/nginx /etc/nginx
ADD assets/etc/service /etc/service

RUN ls -hall /etc/nginx/sites-enabled
RUN ls -hall /etc/nginx/sites-available

RUN rm /etc/nginx/sites-enabled/default # in case of cached build

RUN cd /etc/nginx/sites-enabled && \
    ln -s ../sites-available/default default

VOLUME ["/opt/filesender", "/config/fpm", "/config/filesender", "/config/simplesamlphp/config", "/config/simplesamlphp/metadata", "/opt/filesender/data"]
EXPOSE 80 443

CMD ["/usr/local/sbin/runsvdir-init"]
