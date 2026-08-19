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

# 4. Prepare Laravel (PHP dependencies are installed automatically when the image is built)
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
GET: http://localhost:8000/shipping-rates?country=NL&shipment_date=2026-08-24&package_type=Standard

{
  "data": [
    {
      "carrier": "PostNL",
      "region": "Netherlands",
      "package_type": "Standard",
      "weekend": true,
      "price": "6.95"
    },
    {
      "carrier": "DHL",
      "region": "Netherlands",
      "package_type": "Standard",
      "weekend": true,
      "price": "7.45"
    },
    {
      "carrier": "DPD",
      "region": "Netherlands",
      "package_type": "Standard",
      "weekend": true,
      "price": "7.75"
    }
  ],
  "links": {
    PRUNED
  },
  "meta": {
    PRUNED
  }
}

## What's built

- **Data model**: `Carrier`, `PackageType`, `Region`, `ShippingRate` (soft-deletable) with
  migrations, relationships, and a composite index on `shipping_rates` for search.
- **Seeders**: `DatabaseSeeder` populates the 4 carriers, 3 package types, 4 regions
  (NL, BE, EU, ROW), and all 25 shipping rates from the assignment's rate table.
- **`GET /shipping-rates`** (and identical `GET /api/shipping-rates`): returns shipping
  rates for a given `country`, `shipment_date`, and `package_type` — all three
  parameters are optional and can be combined or omitted freely.
  - `country` (ISO 3166-1 alpha-2) is resolved to a region: `NL`/`BE` map to their own
    region, other EU countries map to `EU`, everything else maps to `ROW`.
  - Carriers are only included on a Saturday/Sunday `shipment_date` if they're flagged
    for weekend delivery; on weekdays the flag is ignored.
  - Results are sorted cheapest first and paginated at 20 per page.
  - Validation lives in `GetShippingRatesRequest`; response shaping in
    `ShippingRateResource`; business logic in `ShippingRateService`, kept out of the
    thin `ShippingRateController`.
- **Tests**: 48 Feature/Unit tests covering the models, seeders' effect on the schema,
  the API and web endpoints (validation, region resolution, weekend rule, ordering,
  pagination, optional parameters), and the service in isolation.

## What's not built

- Authentication
- Versioning prefix
- Rate limiting
- Response caching
- Better country codes support, currently I have EU_COUNTRY_CODES in the service, so if we support another country specifically then we need to update the code, this is best managed in the database.
- Fifferent currency support
- Model Factories
