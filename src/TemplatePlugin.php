<?php

declare(strict_types=1);

namespace SightPress\Plugin\Template;

use SightPress\Plugin\Core\AbstractPlugin;

/**
 * The plugin bundle. The manifest in composer.json declares it. The core loads
 * it, and collects the services it tags through the typed extension points of
 * plugin-core only on sites where the plugin is enabled.
 */
final class TemplatePlugin extends AbstractPlugin
{
}
