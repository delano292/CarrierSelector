# Delano Assignment

AI-assisted development log: [AI_USAGE.md](AI_USAGE.md).

## Setup

Requires:

- Docker Desktop

```bash
# 1. Optionally, if you do not have Docker yet:
# Install it following the official channels https://www.docker.com/get-started/

# 2. Configure the app
cp app/.env.example app/.env

# 3. Bring up the app + two Postgres containers (dev + test)
docker compose up -d --build

# 4. Install PHP dependencies and prepare Laravel
docker compose exec app composer install
docker compose exec app php artisan key:generate

# 5. Migrate + seed the sample data
docker compose exec app php artisan migrate --seed
```

The app is now reachable at `http://localhost:8000`.

## Running the tests

```bash
docker compose exec app php artisan test
```

## Example request/response


## What's built


## What's not built
