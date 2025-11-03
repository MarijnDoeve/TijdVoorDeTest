up *args:
    docker compose up -d {{ args }}

down *args:
    docker compose down --remove-orphans {{ args }}

stop:
    docker compose stop

exec *args:
    docker compose exec php {{ args }}

[no-exit-message]
shell:
    @docker compose exec php bash

bash: shell

migrate: up
    docker compose run --rm php bin/console doctrine:migrations:migrate --no-interaction

fixtures:
    docker compose exec php bin/console doctrine:fixtures:load --purge-with-truncate --no-interaction --group=dev

translations:
    docker compose exec php bin/console translation:extract --force --format=xliff --sort=asc --clean nl

fix-cs:
    docker compose exec php vendor/bin/php-cs-fixer fix
    docker compose exec php vendor/bin/twig-cs-fixer fix

rector *args:
    docker compose exec php vendor/bin/rector {{ args }}

phpstan *args:
    docker compose exec php vendor/bin/phpstan analyse {{ args }}

[confirm]
clean:
    docker compose down -v --remove-orphans
    rm -rf vendor var assets/vendor public/assets public/bundles .php-cs-fixer.cache .twig-cs-fixer.cache

reload-tests:
    @docker compose exec php bin/console --env=test doctrine:database:drop --if-exists --force
    @docker compose exec php bin/console --env=test doctrine:database:create
    @docker compose exec php bin/console --env=test doctrine:migrations:migrate -n
    @docker compose exec php bin/console --env=test doctrine:fixtures:load -n --group=test
