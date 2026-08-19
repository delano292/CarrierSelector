# AI Usage

This project was built with assistance from Claude Code (Anthropic). Below is a
log of the prompts used and a short summary of what was executed for each.

---

### 1. "Goal: create tests for a TDD environment & Carrier, PackageType, Region, ShippingRate skeletons (throwing `Not implemented`)"

> Description: We will create a simple API to receive a list of carriers depending on
> the given country, shipment_date and package_type. The database will hold these
> objects & will be filled by seeder: carriers (name), package types (name), regions
> (name, iso), shipping rates(carrier_id, package_type_id, region_id, weekend (bool) & price).

Created the `carriers`, `package_types`, `regions`, and `shipping_rates` migrations
(with foreign keys) and their Eloquent models. Wrote Feature tests covering
creation, fillable attributes, relationships, the unique `iso` constraint, and
attribute casts. Fixed `phpunit.xml`, which still had Laravel's default SQLite
test config, to point at the project's `postgres_test` Docker service instead.

### 2. "Create database seeders for carrier, packagetype, region and shipping rates. use table below for the data [...]"

Added `CarrierSeeder`, `PackageTypeSeeder`, `RegionSeeder`, and `ShippingRateSeeder`
with the 25 supplied rate rows, wired into `DatabaseSeeder`. Discovered and fixed a
schema mismatch along the way (a `SoftDeletes` change to the models had no matching
migration column) and updated a test that had assumed hard-delete cascade behavior.

### 3. "Lets add the tests for the Api route, Api route: GET getShippingRates (Parameters: Country, Shipment date, Package type) pointing towards ShippingRateController, pointing towards ShippingRateService, using ShippingRate model. It will make use of Form request & resource. For ease of use we'll also add it as a Web route."

Built `GetShippingRatesRequest`, `ShippingRateController`, `ShippingRateService`,
and `ShippingRateResource`, and registered both an API route
(`GET /api/shipping-rates`) and a mirrored web route (`GET /shipping-rates`). The
service resolves a country code to a region (NL/BE/EU/ROW) and applies a
weekend-delivery rule based on the shipment date. Added Feature and Unit tests for
the endpoint, the web mirror, and the service logic in isolation.

### 4. "We'll have to return it cheapest first, paginated (20 per page)"

Updated `ShippingRateService` to order results by price ascending and paginate
them (20 per page). Updated and extended the tests to assert ordering and
pagination behavior, including a second-page scenario.

### 5. "Now lets add indexes for searching shipping_rates. Update the existing migration, we're still in development. Additionally, each GET parameters are optional,"

Added a search index to the existing `shipping_rates` migration and made
`country`, `shipment_date`, and `package_type` all optional on the endpoint, with
the service conditionally applying each filter only when it's supplied. Updated
and added tests to cover every combination of omitted parameters.

### 6. "Update the AI_USAGE with my prompts and a small message what was executed."

This log entry.
