#!/bin/bash

set -e

echo "Running migrations..."
php bin/console doctrine:database:create --if-not-exists --no-interaction
php bin/console doctrine:migrations:migrate --no-interaction

php bin/console doctrine:database:create --if-not-exists --no-interaction --env=test
php bin/console doctrine:migrations:migrate --no-interaction --env=test

exec "$@"