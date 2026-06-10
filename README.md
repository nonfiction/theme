# Nonfiction Theme

Reusable WordPress + Timber theme foundation for Nonfiction projects.

This package provides shared theme infrastructure only: helpers, bootstrapping,
asset enqueueing, Vite manifest parsing, Timber adapters, and WordPress
registration utilities. Site-specific content, templates, blocks, routes, and
design belong in the consuming theme.

## Install

Install from Packagist:

```sh
composer require nonfiction/theme
```

Packagist package: <https://packagist.org/packages/nonfiction/theme>

Use a Composer path repository only while developing this package locally:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../../theme"
    }
  ],
  "require": {
    "nonfiction/theme": "*"
  }
}
```

## Requirements

- PHP `>=7.4`
- WordPress
- `timber/timber` `^2.5.1`
- `icanboogie/inflector` `^4.0`

## Namespace

Classes and helper functions live under `Nonfiction\Theme`.

Composer autoloads:

- `Nonfiction\Theme\` from `src/`
- `src/helpers.php` as an autoloaded file

## Bootstrapping

In the consuming theme, require Composer's autoloader and initialize the shared
theme foundation from your theme entry point, typically `functions.php`:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Nonfiction\Theme\App;

App::init(__DIR__);
App::views(['templates', 'views'], __DIR__);
App::import([
  'src/post-types/*.php',
  'src/blocks/*.php',
], __DIR__);
App::enqueue('dist/.vite/manifest.json', __DIR__);
```

`App::init()` initializes Timber, sets default Timber template locations to
`templates` and `views`, and enables core HTML5 theme support.

## Assets

`App::enqueue()` wires a Vite manifest into WordPress enqueue hooks through
`Nonfiction\Theme\Enqueue` and `Nonfiction\Theme\ViteManifest`.

The manifest may define these asset groups:

- `head`
- `body`
- `blocks`
- `editor`
- `admin`

Each group may expose `css` and `js` entries. Relative `assets/` and `dist/`
paths are resolved against the active theme directory.

Asset helpers are available through `Nonfiction\Theme\Assets`:

```php
use Nonfiction\Theme\Assets;

$url = Assets::asset_uri('assets/img/logo.svg');
$path = Assets::asset_path('assets/img/logo.svg');
```

## Timber Helpers

The package includes reusable Timber adapters:

- `Nonfiction\Theme\Timber\Post`
- `Nonfiction\Theme\Timber\Block`
- `Nonfiction\Theme\Timber\Menu`
- `Nonfiction\Theme\Timber\MenuItem`

Extend `Nonfiction\Theme\Timber\Post` in the consuming theme to create
class-backed post types:

```php
<?php

use Nonfiction\Theme\Timber\Post;

class Project extends Post {
  protected static $register_post_type = [
    'name' => 'project',
    'has_archive' => false,
    'supports' => ['title', 'editor', 'thumbnail', 'revisions'],
  ];
}

Project::__constructStatic();
```

Post types register through WordPress core APIs. Archives are not assumed by
default; consuming themes can use normal pages and custom blocks for listing
experiences.

Register blocks with `Nonfiction\Theme\Timber\Block` when you want a PHP/Twig
render callback backed by WordPress block registration:

```php
use Nonfiction\Theme\Timber\Block;

Block::register_block_type([
  'name' => 'nonfiction/example',
  'render' => function (array $context) {
    return 'blocks/example.twig';
  },
]);
```

## WordPress Registrars

Direct WordPress registration helpers live under `Nonfiction\Theme\WordPress`:

- `PostTypeRegistrar`
- `TaxonomyRegistrar`
- `BlockTypeRegistrar`
- `Meta`

The Timber adapters delegate to these registrars. Use the registrars directly
only when the consuming theme needs lower-level control.

## Helpers

Autoloaded helpers include inflection, import/merge, request sanitization,
CSS-string, date, string-prefix/suffix, and relative-link helpers.

Example:

```php
use function Nonfiction\Theme\make_link_relative;
use function Nonfiction\Theme\pluralize;

$label = pluralize('project');
$url = make_link_relative(home_url('/projects/'));
```

## Development

Validation commands for this package:

```sh
composer validate --no-check-publish --no-check-lock
for f in $(git ls-files '*.php'); do php -l "$f" || exit 1; done
composer dump-autoload
```

Do not commit `vendor/` or `composer.lock`; this repository is a library.
