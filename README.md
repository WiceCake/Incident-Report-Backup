# Deployment

- Ubuntu
```
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php83-composer:latest composer install --ignore-platform-reqs
```

- Windows
```
docker run --rm -v "$(PWD):/var/www/html" -w /var/www/html laravelsail/php83-composer:latest composer install --ignore-platform-reqs
```

## Setting up webapp
- Entering webapp container
```
docker compose exec webapp sh
```
- Copy .env file
```
cp .env.example.env .env

nano .env

Note: Change only the following environment variable
DB_HOST -> change the value into "mysql"
DB_DATABASE -> put any database name
DB_USERNAME -> put any username except "root"
DB_PASSWORD -> put any password except "root"
```

- Run ``php artisan key:generate``
- Run ``php artisan migrate --seed``

## No Assets Showing Up

- Run ``php artisan storage:link``

## Storage Path cannot be accessed

- Run ``sudo chmod -R 777 storage/``
