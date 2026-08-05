ARG PHP_VERSION
FROM dunglas/frankenphp:1-php${PHP_VERSION}-trixie

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# reference: https://github.com/mlocati/docker-php-extension-installer
RUN install-php-extensions \
    intl \
    pdo_mysql \
    gd \
    redis

RUN apt update \
    && apt install -y git unzip mariadb-client \
    && apt clean \
    && rm -rf /var/lib/apt/lists/*

ARG USER=www-data

# Give write access to /config/caddy and /data/caddy
RUN \
  useradd ${USER}; \
  chown -R ${USER}:${USER} /config/caddy /data/caddy

# Install Neos base distribution
ARG NEOS_VERSION
RUN rm -rf /app \
  && composer create-project neos/neos-base-distribution:${NEOS_VERSION} /app

# Add config files
COPY Tests/E2E/system_under_test/sut_file_system_overrides/ /

ARG ENTRY_POINT_FILE
COPY ${ENTRY_POINT_FILE} /entrypoint.sh

# chown for neos data folder and Resources ONLY
RUN mkdir -p /app/Data  /app/Web/_Resources \
    && chown -R ${USER} /app \
    && chmod +x /entrypoint.sh

WORKDIR /app
USER ${USER}

ENTRYPOINT ["/entrypoint.sh"]
