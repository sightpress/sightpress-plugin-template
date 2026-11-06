import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';
import { readFileSync } from 'node:fs';

type Manifest = { id: string; routes: string[]; admin_routes: string[]; locales: string[] };

const composer = JSON.parse(readFileSync(new URL('../composer.json', import.meta.url), 'utf8'));
const manifest: Manifest = composer.extra.sightpress;

const baseUrl = process.env.SIGHTPRESS_BASE_URL;

// Routes with {parameters} need a fixture to fill them; the plugin adds those
// URLs to e2e/fixtures.json as {"<route pattern>": "<url>"} when it has content.
let fixtures: Record<string, string> = {};
try {
  fixtures = JSON.parse(readFileSync(new URL('./fixtures.json', import.meta.url), 'utf8'));
} catch {
  fixtures = {};
}

const urls = [...manifest.routes, ...manifest.admin_routes].flatMap((pattern) => {
  if (!pattern.includes('{')) {
    return [pattern];
  }
  return fixtures[pattern] ? [fixtures[pattern]] : [];
});

test.describe(`${manifest.id}: every route passes axe-core`, () => {
  test.skip(!baseUrl, 'Set SIGHTPRESS_BASE_URL to a running site with this plugin enabled.');

  test('the plugin declares at least the routes it serves', () => {
    expect(Array.isArray(manifest.routes)).toBe(true);
    expect(Array.isArray(manifest.admin_routes)).toBe(true);
  });

  for (const url of urls) {
    test(`${url} has no WCAG 2.2 AA violations`, async ({ page }) => {
      await page.goto(url);
      await expect(page.locator('h1')).toHaveCount(1);

      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
        .analyze();

      expect(results.violations).toEqual([]);
    });
  }
});
