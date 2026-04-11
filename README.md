
Caching layer for Laravel.

## Features
- Adaptive TTL 
- Tag-based cache 
- Automatic compression for heavey payloads
- Query-level caching via Eloquent macros
- Built-in analytics 
- Redis support


## Usage
use CacheWrapper;

CacheWrapper::remember('key', fn() => 'value');

## Eloquent Queries
User::where('active', 1)->cache()->get();

## Testing
vendor/bin/phpunit


![CI](https://github.com/YOUR_USERNAME/YOUR_REPO/actions/workflows/tests.yml/badge.svg)


