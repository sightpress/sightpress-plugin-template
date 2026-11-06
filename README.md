# SightPress plugin template

`sightpress/plugin-template`: the starting point for a new SightPress plugin.
Licensed GPL-2.0-or-later.

> **Status:** M0 scaffolding complete. `bin/setup` generates a plugin that
> passes the full quality bar with the plugin-core test kit; the tests that need
> the core's test kernel skip until `sightpress/core` ships it.

```bash
composer create-project sightpress/plugin-template acme-events
cd acme-events
php bin/setup     # asks for the plugin id, vendor, namespace and name
```

Without a terminal, give the answers as options:
`php bin/setup --id=acme.events --name=Events --vendor=acme --namespace='Acme\Events'`.
Official plugins use a bare id and the vendor `sightpress`. To scaffold an
existing checkout, run
`php bin/setup --id=form-builder --name=FormBuilder --target=../plugin-form-builder --keep-readme`.

You get a plugin that already passes the full SightPress bar:

- `composer.json` with the manifest under `extra.sightpress`, requiring
  `sightpress/plugin-core` (and `sightpress/core` for tests only);
- the bundle class extending `AbstractPlugin`, and `Contract/`, `Domain/`,
  `Application/`, `Infrastructure/` and `UI/` folders;
- `migrations/`, `templates/` (extending the core layouts), `translations/`
  (XLIFF catalogues for all 11 launch interface languages, with English
  (United Kingdom) as the source) and `assets/` (Stimulus, enhancement only).
  Official plugins keep all 11. A third-party plugin only needs English
  (United Kingdom): remove the catalogues you won't maintain and their codes
  from `extra.sightpress.locales`, and the checks cover only what's left;
- tests on `PluginTestCase`, enabled and disabled checks, and Playwright + axe
  on every manifest route (skipped with a reason until a site is served);
- Deptrac (a plugin depends only on `plugin-core` and the `Contract\` of the
  plugins it requires, never on the core), PHPStan at the maximum level,
  PHP-CS-Fixer and Rector;
- CI that runs every gate, plus the browser tests.

## Development

```bash
COMPOSER=composer.dev.json composer install   # sibling checkouts of plugin-core and core
composer qa                                    # tests, static analysis, style, Rector, Deptrac, translations
SIGHTPRESS_TEMPLATE_FULL_CHECK=1 vendor/bin/phpunit tests/Template   # generate a plugin and run every gate on it
```

Official plugins (`plugin-articles`, `plugin-community-helper`, `plugin-form-builder`,
`plugin-newsletter-manager`, `plugin-notifications-helper`, `plugin-image-finder`, `plugin-ai-helper`,
`plugin-file-importer`, …) are made from this template. When the plugin conventions
change, change the template in the same change set.
