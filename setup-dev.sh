#!/bin/bash

function setup_dev_commands {
    composer install
    npm install
    npm run build

    php artisan livewire:publish --assets

    DB_DATABASE="${DB_DATABASE:-"/app/storage/app/database.sqlite"}"
    if [ "$DB_CONNECTION" = "sqlite" ] && [ ! -f "$DB_DATABASE" ]; then
        mkdir -p "$(dirname "$DB_DATABASE")"
        touch "$DB_DATABASE"
    fi

    php artisan key:generate
    php artisan migrate:fresh --seed
    php artisan storage:link
}

cp .env.development .env
docker compose -f compose.dev.yaml up -d --build
docker exec -u application survey bash -c "$(declare -f setup_dev_commands); setup_dev_commands"

exit 0
