<?php

declare(strict_types=1);

namespace SightPress\Plugin\Template\Tests;

use SightPress\Plugin\Core\TestKit\PluginTestCase;

/**
 * The contract checks every plugin must pass: a valid manifest, an `api`
 * constraint that matches the plugin-core requirement, no use of core classes,
 * complete translations and GPL-compatible dependencies.
 */
class PluginTest extends PluginTestCase
{
    use SkipsInsideTemplate;

    protected function pluginRoot(): string
    {
        return \dirname(__DIR__);
    }

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->skipWhenTemplateItself($this->pluginRoot().'/composer.json');
    }
}
