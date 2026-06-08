# AGENTS.md

## Package purpose

- Reusable WordPress + Timber theme foundation for Nonfiction projects.
- Scope is package-level infrastructure only: helpers, bootstrapping, assets, Timber adapters, and WordPress registrars.
- Do not add client-specific content, design, routes, seeded pages, or project conventions here.

## Namespace and autoload

- Namespace: `Nonfiction\Theme`
- Composer autoload:
  - PSR-4 `Nonfiction\Theme\` -> `src/`
  - `autoload.files` includes `src/helpers.php`
- Code in `src/` is the source of truth.

## Dependency direction

- This package must not depend on project namespaces such as `nf\*`.
- Consuming projects import classes/functions from this package, not the reverse.
- Keep extraction clean: no reverse dependencies on Sanjel application code.

## Current dependencies

- PHP `>=7.4`
- `timber/timber`
- `icanboogie/inflector`
- Dev only: `friendsofphp/php-cs-fixer`

## Key files and responsibilities

- `src/helpers.php`: shared helpers for inflection, import/merge, CSS/string/request helpers, and `make_link_relative()`.
- `src/App.php`: boot/import/views/enqueue/flush orchestration.
- `src/Assets.php`, `src/Enqueue.php`, `src/ViteManifest.php`: asset URL normalization, enqueue wiring, and manifest parsing.
- `src/Timber/Post.php`, `src/Timber/Block.php`, `src/Timber/Menu.php`, `src/Timber/MenuItem.php`: Timber layer.
- `src/WordPress/PostTypeRegistrar.php`, `src/WordPress/TaxonomyRegistrar.php`, `src/WordPress/BlockTypeRegistrar.php`, `src/WordPress/Meta.php`: direct WordPress registration layer.

## Architecture boundaries

- Timber wrappers should delegate direct WordPress registration to the `WordPress/` layer.
- Helpers are shared across the package; keep them generic.
- Site-specific CPTs, blocks, templates, menus, and config belong in the consuming theme.
- `has_archive` must not be assumed for listing pages; consuming projects may use normal pages with custom blocks.

## Historical decisions and constraints

- `johnbillion/extended-cpts` was intentionally removed; use direct WordPress APIs.
- GraphQL support was intentionally dropped.
- Taxonomy/meta/CMB2 support is preserved.
- No compatibility shims for old `nf\*` classes; migrations should be intentional.
- Preserve Vite manifest concepts and parsing behavior for `head`, `body`, `blocks`, `editor`, and `admin` asset groups.

## Development rules

- Prefer minimal diffs and PHP 7.4-compatible syntax.
- Use short arrays and avoid unnecessary dependencies.
- Preserve existing WordPress/Timber behavior unless runtime checks prove a safe change.
- Keep package extraction clean; do not add consumer-specific behavior.
- Do not commit `vendor/`.
- `composer.lock` is ignored here.

## Validation

- `composer validate --no-check-publish`
- `for f in $(git ls-files '*.php'); do php -l "$f" || exit 1; done`
- `composer dump-autoload`
- If checking the client consumer, run from `/home/jon/src/nonfiction/sanjel`:
  - `composer --working-dir=theme dump-autoload`
  - runtime autoload checks as needed

## Composer and repo notes

- Package name: `nonfiction/theme`
- Suggested repo: `github.com/nonfiction/theme`
- Local development can use the path repository from the client consumer; later this can switch to VCS or Packagist.
