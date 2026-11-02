# SightPress plugin template

`sightpress/plugin-template`: the starting point for a new SightPress plugin.
Licensed GPL-2.0-or-later.

> **Status:** pre-M0.

```bash
composer create-project sightpress/plugin-template acme-events
cd acme-events
php bin/setup     # asks for the plugin id, vendor, namespace and name
```

You get a plugin that already passes the full SightPress bar:

- `composer.json` with the manifest under `extra.sightpress`, requiring
  `sightpress/plugin-core` (and `sightpress/core` for tests only);
- the bundle class extending `AbstractPlugin`, and `Contract/`, `Domain/`,
  `Application/`, `Infrastructure/` and `UI/` folders;
- `migrations/`, `templates/` (extending the core layouts), `translations/`
  (XLIFF catalogues for all 13 launch interface languages, with English
  (United Kingdom) as the source) and `assets/` (Stimulus, enhancement only);
- tests on `PluginTestCase`, Playwright + axe on every manifest route, Deptrac,
  PHPStan and PHP-CS-Fixer config;
- CI that runs everything with the plugin enabled and disabled, against the
  latest core release and core's main branch.

Official plugins (`plugin-articles`, `plugin-community`, `plugin-forms`,
`plugin-newsletter`, `plugin-notifications`, `plugin-image-finder`, …) are made from this template. The full specification is
in `plugins.md`.
