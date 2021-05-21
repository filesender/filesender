FROM php:7.4-fpm-buster

# Accept arguments to make the build process dynamically accept different builds
# Used for filesender CI

ARG REF=2.22
ARG REF_SUM="ef6da83e7ac46303e5f8d620f6858565fd746d7c4b054a12439a561d79b515a2"
ARG SSP_REF=1.19.0
ARG SSP_REF_SUM="2111129787a4baf27a51e52bda660c56d069f167354800bffc72440dcacb3a6f"

ENV FILESENDER_VERSION=$REF \
    FILESENDER_SUM=$REF_SUM \
    SSP_VERSION=$SSP_REF \
    SSP_SUM=$SSP_REF_SUM

RUN cd /opt && mkdir filesender && \
    cd /opt/filesender && \
    curl -kL https://github.com/filesender/filesender/archive/master-filesender-${FILESENDER_VERSION}.tar.gz | tar xz && \
    ln -s filesender-master-filesender-${FILESENDER_VERSION} filesender && \
    curl -L https://github.com/simplesamlphp/simplesamlphp/releases/download/v${SSP_VERSION}/simplesamlphp-${SSP_VERSION}.tar.gz | tar xz && \
    ln -s simplesamlphp-${SSP_VERSION} simplesamlphp

RUN cd /opt/filesender/filesender && \
    cp config/config_sample.php config/config.php && \
    mkdir -p tmp files log && \
    chmod o-rwx tmp files log config/config.php && \
    chown www-data:www-data tmp files log && \
    chgrp www-data config/config.php && \
    cd /opt/filesender/simplesamlphp && \
    cp -r config-templates/*.php config/ && \
    cp -r metadata-templates/*.php metadata/

RUN mkdir -p /config/fpm /config/filesender /config/simplesamlphp/config /config/simplesamlphp/metadata && \
    mv -f /usr/local/etc/php-fpm.d/www.conf /config/fpm/www.conf && \
    mv -f /opt/filesender/filesender/config/config.php /config/filesender/config.php && \
    mv -f /opt/filesender/simplesamlphp/config/*.php /config/simplesamlphp/config/ && \
    mv -f /opt/filesender/simplesamlphp/metadata/*.php /config/simplesamlphp/metadata/ && \
    ln -s /config/fpm/www.conf /usr/local/etc/php-fpm.d/filesender.conf && \
    ln -s /config/filesender/config.php /opt/filesender/filesender/config/config.php && \
    ln -s /config/simplesamlphp/config/acl.php /opt/filesender/simplesamlphp/config/acl.php && \
    ln -s /config/simplesamlphp/config/authsource.php /opt/filesender/simplesamlphp/config/authsource.php && \
    ln -s /config/simplesamlphp/config/config.php /opt/filesender/simplesamlphp/config/config.php && \
    ln -s /config/simplesamlphp/metadata/active.php /opt/filesender/simplesamlphp/metadata/active.php
 
RUN apt-get update && \
    apt-get dist-upgrade -y && \
    apt-get install -y --no-install-recommends nginx runit && \
    apt-get autoremove -y && \
    apt-get clean && \ 
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY assets /
    
VOLUME ["/opt/filesender", "/config/fpm", "/config/filesender", "/config/simplesamlphp/config", "/config/simplesamlphp/metadata", "/opt/filesender/data"]
EXPOSE 80 443

CMD ["/usr/local/sbin/runsvdir-init"]

ARG BUILD_DATE
ARG VCS_REF
LABEL maintainer="Nils Vogels <n.vogels@aves-it.nl>" \
      org.label-schema.build-date="${BUILD_DATE}" \
      org.label-schema.docker.dockerfile="/docker/Dockerfile" \
      org.label-schema.license="BSD3" \
      org.label-schema.name="Filesender and SimpleSamlPHP on php7-fpm" \
      org.label-schema.vendor="filesender" \
      org.label-schema.url="https://filesender.org" \
      org.label-schema.vcs-ref=$VCS_REF \
      org.label-schema.vcs-url="https://github.com/filesender/filesender.git" \
      org.label-schema.vcs-type="Git" \
      org.label-schema.version="${REF}" \
      org.label-schema.schema-version="1.0"
