<?php

declare(strict_types=1);

namespace SightPress\Plugin\Template\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests that need the core's test kernel: a real site, the plugin enabled or
 * disabled on it, a database. They skip until sightpress/core ships that
 * kernel (milestone M6) and run for real as soon as it is installed.
 */
final class EnabledDisabledTest extends TestCase
{
    private const string CORE_KERNEL = 'SightPress\Core\Testing\PluginKernelTestCase';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(self::CORE_KERNEL)) {
            self::markTestSkipped(\sprintf('The core test kernel (%s) is not installed. Install sightpress/core (require-dev) to run the enabled and disabled checks.', self::CORE_KERNEL));
        }
    }

    public function testPluginRoutesAnswer404WhenThePluginIsDisabledOnTheSite(): void
    {
        self::markTestIncomplete('Waiting for the core test kernel API (M6): request each manifest route on a site where the plugin is off.');
    }

    public function testCoreStillWorksWhenThePluginIsDisabled(): void
    {
        self::markTestIncomplete('Waiting for the core test kernel API (M6): boot a site with the plugin off and request the home page.');
    }

    public function testPluginRoutesWorkWhenThePluginIsEnabledOnTheSite(): void
    {
        self::markTestIncomplete('Waiting for the core test kernel API (M6): request each manifest route on a site where the plugin is on.');
    }

    public function testSiteOwnedEntitiesNeverLeakAcrossSites(): void
    {
        self::markTestIncomplete('Waiting for the core test kernel API (M6): use assertSiteIsolation() for every site-owned entity of the plugin.');
    }
}
