up:
    docker compose up -d

down *args:
    docker compose down {{ args }} --remove-orphans

stop:
    docker compose stop

exec *args:
    docker compose exec php {{ args }}

[no-exit-message]
shell:
    @docker compose exec php bash

migrate: up
    docker compose run --rm php bin/console doctrine:migrations:migrate --no-interaction

fixtures:
    docker compose exec php bin/console doctrine:fixtures:load --purge-with-truncate --no-interaction

translations:
    docker compose exec php bin/console translation:extract --domain=messages --force --format=yaml --sort=asc --clean nl

fix-cs:
    docker compose exec php vendor/bin/php-cs-fixer fix
    docker compose exec php vendor/bin/twig-cs-fixer fix

rector *args:
    docker compose exec php vendor/bin/rector {{ args }}

phpstan *args:
    docker compose exec php vendor/bin/phpstan analyse {{ args }}
