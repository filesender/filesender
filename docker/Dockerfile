FROM php:8.5-fpm
LABEL version="0.1.0" \
      license="BSD3" \
      maintainer="Richard Freitag <freitag@sunet.se>" \
      description="Filesender and simplesamlphp based on php8.5-fpm" \
      documentation="https://docs.filesender.org"

ARG FILESENDER_VERSION=3.3
ARG SSP_VERSION=2.4.4
ARG FILESENDER_BRANCH=development

RUN apt-get update && \
    apt-get dist-upgrade -y && \
    apt-get install -y --no-install-recommends \
        nginx \
        wget \
        nano \
        mariadb-client \
        procps \
        net-tools \
        nmap \
        libicu-dev \
        curl \
        gnupg2 \
        ca-certificates \
        lsb-release \
        debian-keyring \
        runit && \
    apt-get autoremove -y && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

    RUN mkdir -p /opt/filesender/
    RUN if [ "$FILESENDER_VERSION" = "branch" ]; then \
            echo "Building $FILESENDER_VERSION from $FILESENDER_BRANCH"; \
            wget -O /opt/filesender/filesender.tar.gz filesender.tar.gz https://github.com/filesender/filesender/archive/refs/heads/$FILESENDER_BRANCH.tar.gz; \
            cd /opt/filesender && \
            tar xzf filesender.tar.gz && \
            mv filesender-${FILESENDER_BRANCH} filesender; \
        elif [ "$FILESENDER_VERSION" = "localdev" ]; then \
            echo "Building $FILESENDER_VERSION from current local directory is not implemented yet!"; exit 1; \
            # ADD /tmp/filesender-localdev.tar.gz /opt/filesender/filesender.tar.gz; \
        else \
            echo "Building Filesender version $FILESENDER_VERSION";  \
            wget -O /opt/filesender/filesender.tar.gz https://github.com/filesender/filesender/archive/refs/tags/$FILESENDER_VERSION.tar.gz; \
            cd /opt/filesender && \
            ls -hall && \
            tar xzf filesender.tar.gz && \
            mv filesender-${FILESENDER_VERSION} filesender; \
        fi

RUN docker-php-ext-configure intl && \
    docker-php-ext-install mysqli pdo pdo_mysql intl && \
    docker-php-ext-enable pdo_mysql

RUN cd /opt/filesender && \
    wget -O simplesamlphp.tar.gz https://github.com/simplesamlphp/simplesamlphp/releases/download/v${SSP_VERSION}/simplesamlphp-${SSP_VERSION}-slim.tar.gz && \
    tar xzf simplesamlphp.tar.gz && \
    mv simplesamlphp-${SSP_VERSION} simplesamlphp

ADD assets/opt/filesender/filesender/config/config.php /opt/filesender/filesender/config/config.php
ADD filesender-config.js.php /opt/filesender/filesender/www/filesender-config.js.php

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

RUN ln -s /config/simplesamlphp/metadata/saml20-idp-hosted.php.dist /opt/filesender/simplesamlphp/metadata/saml20-idp-hosted.php
RUN ln -s /config/simplesamlphp/metadata/saml20-sp-remote.php.dist /opt/filesender/simplesamlphp/metadata/saml20-sp-remote.php
RUN ln -s /config/simplesamlphp/metadata/saml20-idp-remote.php.dist /opt/filesender/simplesamlphp/metadata/saml20-idp-remote.php

ADD assets/config/simplesamlphp/config/authsources.php.dist /config/simplesamlphp/config/authsources.php.dist
ADD assets/config/simplesamlphp/config/config.php.dist /config/simplesamlphp/config/config.php.dist
ADD assets/config/simplesamlphp/metadata/saml20-idp-hosted.php.dist /config/simplesamlphp/metadata/saml20-idp-hosted.php.dist
ADD assets/config/simplesamlphp/metadata/saml20-sp-remote.php.dist /config/simplesamlphp/metadata/saml20-sp-remote.php.dist
ADD assets/config/simplesamlphp/metadata/saml20-idp-remote.php.dist /config/simplesamlphp/metadata/saml20-idp-remote.php.dist

ADD assets/config/simplesamlphp/cert/localhost.crt /opt/filesender/simplesamlphp/cert/localhost.crt
ADD assets/config/simplesamlphp/cert/localhost.pem /opt/filesender/simplesamlphp/cert/localhost.pem

ADD AuditLog.class.php.3.3.patch /opt/filesender/filesender/classes/data/AuditLog.class.php.3.3.patch
ADD GUI.class.php.3.3.patch /opt/filesender/filesender/classes/utils/GUI.class.php.3.3.patch
ADD transfer_detail_page.php.3.3.patch /opt/filesender/filesender/templates/transfer_detail_page.php.3.3.patch

# ADD AuditLog.class.php /opt/filesender/filesender/classes/data/AuditLog.class.php
# ADD AuditLog.class.php /opt/filesender/filesender/classes/data/AuditLog.class.php.full
# ADD GUI.class.php /opt/filesender/filesender/classes/utils/GUI.class.php.full

RUN if [ "$FILESENDER_VERSION" = "3.3" ]; then \
      echo "Apply Filesender 3.3 patches"; \
      patch -f /opt/filesender/filesender/classes/data/AuditLog.class.php < /opt/filesender/filesender/classes/data/AuditLog.class.php.3.3.patch; \
      patch -f /opt/filesender/filesender/classes/utils/GUI.class.php < /opt/filesender/filesender/classes/utils/GUI.class.php.3.3.patch; \
      patch -f /opt/filesender/filesender/templates/transfer_detail_page.php < /opt/filesender/filesender/templates/transfer_detail_page.php.3.3.patch; \
    else \
      echo "Not patching $$FILESENDER_VERSION"; \
    fi

RUN cd /opt/filesender/filesender && \
    # cp config/config_sample.php config/config.php && \
    mkdir -p tmp files log /var/cache/simplesamlphp/core /var/cache/simplesamlphp/admin /var/cache/simplesamlphp/saml && \
    chmod o-rwx tmp files log config/config.php && \
    chown -R www-data:www-data tmp files log /var/cache/simplesamlphp/core /var/cache/simplesamlphp/admin /var/cache/simplesamlphp/saml && \
    # chown -R www-data:www-data config && \
    chgrp www-data config/config.php

RUN cd /opt/filesender/simplesamlphp/cert && \
    chgrp www-data ./localhost.crt ./localhost.pem && \
    chmod g+r ./localhost.pem

# RUN cd /opt/filesender/simplesamlphp/ && \
#     chown -R www-data:www-data cert

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
