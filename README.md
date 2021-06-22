# PSR-6: Caching Interface for Drupal 8+

## Installation

```shell
composer require itk-dev/drupal_psr6_cache
vendor/bin/drush pm:enable drupal_psr6_cache
```

or add a composer dependency on `itk-dev/drupal_psr6_cache` (see below for
details).

## Usage

Add a dependency on the `drupal_psr6_cache` module to a module:

```json
# composer.json
{
    "name": "drupal/my_module",
    …
    "require": {
        "itk-dev/drupal_psr6_cache": "^1.0"
    }
}
```

```yml
# my_module.info.yml
…
dependencies:
  - drupal:drupal_psr6_cache
```

Inject the cache pool into a service:

```yml
# my_module.services.yml
my_module.some_service:
  class: Drupal/my_module/SomeService.php
  arguments:
    - '@drupal_psr6_cache.cache_item_pool'
```

Use the cache pool:

```php
// src/SomeService.php
namespace Drupal/my_module;

use Psr\Cache\CacheItemPoolInterface;

class SomeService {
  __construct(CacheItemPoolInterface $cacheItemPool) {
    …
  }
}
```

## Development

### Tests

@todo

### Coding standards

The code must follow the [Drupal coding
standards](https://www.drupal.org/docs/develop/standards).

Check coding standards (run `composer install` to install the required tools):

```shell
composer coding-standards-check
```

Apply coding standards:

```shell
composer coding-standards-apply
```

## References and inspiration

<https://git.drupalcode.org/project/drupal_psr_cache>
