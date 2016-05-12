# Project-EC

## Run

```bash
cd docker
docker-compose up -d
```

## Stop

```bash
cd docker
docker-compose down
```

# Composer

```bash
docker-compose run --rm composer create-project laravel/laravel /data/www --prefer-dist
```

# Artisan

```bash
docker-compose run --rm artisan migrate:refresh --seed
```