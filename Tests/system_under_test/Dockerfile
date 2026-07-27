ARG PHP_VERSION
FROM dunglas/frankenphp:1-php${PHP_VERSION}-trixie

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# reference: https://github.com/mlocati/docker-php-extension-installer
RUN install-php-extensions \
    intl \
    bcmath \
    opcache \
    pdo \
    pdo_mysql \
    xsl \
    ffi \
    vips \
    redis

RUN apt update \
    && apt install -y git unzip mariadb-client \
    && apt clean \
    && rm -rf /var/lib/apt/lists/*

ARG USER=www-data

# Give write access to /config/caddy and /data/caddy
RUN \
  useradd ${USER}; \
  chown -R ${USER}:${USER} /config/caddy /data/caddy \
  && touch /var/run/caretakerd.key && chown ${USER}:${USER} /var/run/caretakerd.key

# HOTFIX for ARM64 Architectures and VIPS (see https://github.com/opencv/opencv/issues/14884#issuecomment-706725583 for details)
# only needed for development on Apple Silicon Macs
RUN echo '. /etc/bash.vips-arm64-hotfix.sh' >>  /etc/bash.bashrc

# Install Neos base distribution
# NEOS_VERSION is a full composer version constraint (not just a major version number) so each
# variant can pin as tightly as it needs to - a floating "^9" constraint previously picked up
# neos-base-distribution 9.1.7, whose own composer.json requires neos/buildessentials ~9.1.0,
# but that package was never stable-tagged past 9.0.0 (only a 9.1.x-dev branch exists), breaking
# the build with a minimum-stability error.
ARG NEOS_VERSION
RUN rm -rf /app \
  && composer create-project neos/neos-base-distribution:${NEOS_VERSION} /app

RUN composer require rokka/imagine-vips:0.*

# Add config files
COPY Tests/system_under_test/sut_file_system_overrides/ /

ARG ENTRY_POINT_FILE
COPY ${ENTRY_POINT_FILE} /entrypoint.sh

# chown for neos data folder and Resources ONLY
RUN mkdir -p /app/Data  /app/Web/_Resources \
    && chown -R ${USER} /app \
    && chmod +x /entrypoint.sh

WORKDIR /app
USER ${USER}

ENTRYPOINT ["/entrypoint.sh"]
