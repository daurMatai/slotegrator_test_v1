- `docker compose up --build`
- `docker exec slotegrator_php composer install`
- `docker exec slotegrator_php php bin/console doctrine:database:create --if-not-exists --no-interaction`
- `docker exec slotegrator_php php bin/console doctrine:migrations:migrate --no-interaction`
- `docker exec slotegrator_php php bin/console doctrine:database:create --if-not-exists --no-interaction --env=test`
- `docker exec slotegrator_php php bin/console doctrine:migrations:migrate --no-interaction --env=test`

В корне есть папка slotegrator.postman_collection.json для апи запросов