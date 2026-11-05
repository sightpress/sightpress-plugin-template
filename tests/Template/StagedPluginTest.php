<?php

declare(strict_types=1);

namespace SightPress\Plugin\Template\Tests\Template;

use SightPress\Plugin\Template\Tests\PluginTest;

/**
 * Template only (bin/setup removes this folder). Runs the shared plugin
 * checks against a staged copy whose composer.json has the final package
 * type, so the template is held to the same contract as a generated plugin.
 */
final class StagedPluginTest extends PluginTest
{
    private static string $staged;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        $root = \dirname(__DIR__, 2);
        self::$staged = $root.'/var/staged';

        if (!is_dir(self::$staged)) {
            mkdir(self::$staged, 0o777, true);
        }

        $composer = (string) file_get_contents($root.'/composer.json');
        file_put_contents(self::$staged.'/composer.json', str_replace('"type": "project"', '"type": "sightpress-plugin"', $composer));

        foreach (['src', 'translations', 'vendor'] as $folder) {
            $link = self::$staged.'/'.$folder;
            if (!is_link($link) && is_dir($root.'/'.$folder)) {
                symlink($root.'/'.$folder, $link);
            }
        }
    }

    #[\Override]
    protected function pluginRoot(): string
    {
        return self::$staged;
    }
}
