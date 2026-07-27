#!/bin/bash
set -eou pipefail

# Register local path repository and require local package
# The code will be mounted into the container by docker-compose, so we can use it as a path repository
composer config repositories.sandstorm-2fa \
  '{"type":"path","url":"/app/DistributionPackages/Sandstorm.UserManagement","options":{"symlink":true}}' \
  && composer require sandstorm/usermanagement:@dev

echo "Waiting for database..."
until mariadb -h"${DB_NEOS_HOST}" -P"${DB_NEOS_PORT}" -u"${DB_NEOS_USER}" -p"${DB_NEOS_PASSWORD}" -D"${DB_NEOS_DATABASE}" --disable-ssl --silent -e "SELECT 1;" 1>/dev/null 2>/dev/null; do
  sleep 2
done
echo "Database is ready."

./flow flow:cache:flush

./flow doctrine:migrate

yes y | ./flow resource:clean || true

./flow site:import --package-key Neos.Demo

./flow resource:publish --collection static

frankenphp run --config /etc/frankenphp/Caddyfile
