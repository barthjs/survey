#!/bin/bash

function setup_dev_commands {
    composer install
    npm install
    npm run build

    php artisan livewire:publish --assets

    php artisan key:generate
    php artisan migrate:fresh --seed
}

cp .env.development .env
docker compose -f compose.dev.yaml up -d --build
docker exec -u application survey bash -c "$(declare -f setup_dev_commands); setup_dev_commands"

exit 0
