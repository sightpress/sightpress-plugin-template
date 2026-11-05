<?php

declare(strict_types=1);

namespace SightPress\Plugin\Template\Tests;

/**
 * The template's composer.json has type "project" so that create-project accepts it,
 * and the manifest reader needs "sightpress-plugin". Tests that read the manifest skip
 * inside the template itself and run as soon as bin/setup has generated a plugin.
 * Harmless in a generated plugin: the type is then always "sightpress-plugin".
 */
trait SkipsInsideTemplate
{
    private function skipWhenTemplateItself(string $composerJson): void
    {
        $composer = json_decode((string) file_get_contents($composerJson), true, 512, \JSON_THROW_ON_ERROR);
        if (\is_array($composer) && 'sightpress-plugin' !== ($composer['type'] ?? null)) {
            self::markTestSkipped('This is the template itself (type "project"). These checks run on a staged copy in StagedPluginTest and on every generated plugin.');
        }
    }
}
