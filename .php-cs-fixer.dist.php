<?php

declare(strict_types=1);

use PhpCsFixer\Finder;
use SightPress\Plugin\Core\Quality\CsFixerRules;

require __DIR__.'/vendor/autoload.php';

$finder = Finder::create()
    ->in([__DIR__.'/src', __DIR__.'/tests'])
    ->append(array_filter([__DIR__.'/bin/setup', __DIR__.'/rector.php'], is_file(...)));

return CsFixerRules::config($finder);
