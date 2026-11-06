<?php

declare(strict_types=1);

namespace SightPress\Plugin\Template\Tests\Template;

use PHPUnit\Framework\TestCase;
use SightPress\Plugin\Core\Manifest\ManifestReader;
use SightPress\Plugin\Core\Value\Locale;

/**
 * Template only. Proves that bin/setup produces a plugin that passes the full bar.
 *
 * The generated plugin goes into a sibling folder of the template, so the path
 * repositories in composer.dev.json (../plugin-core, ../core) still resolve.
 * The quick checks always run. The full bar (composer install, then PHPUnit,
 * PHPStan, PHP-CS-Fixer, Rector, Deptrac, licence and translation checks) runs
 * with SIGHTPRESS_TEMPLATE_FULL_CHECK=1 because it installs dependencies.
 */
final class GeneratedPluginTest extends TestCase
{
    private string $generated;

    #[\Override]
    protected function setUp(): void
    {
        $this->generated = \dirname(__DIR__, 3).'/.generated-plugin-'.bin2hex(random_bytes(4));
        mkdir($this->generated);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->remove($this->generated);
    }

    public function testThirdPartyPluginIsRenamedEverywhere(): void
    {
        $this->generate(['--id=acme.events', '--name=Events', '--vendor=acme', '--namespace=Acme\Events', '--description=Events for SightPress.']);

        $composer = $this->composer();
        self::assertSame('acme/plugin-events', $composer['name']);
        self::assertSame('sightpress-plugin', $composer['type']);
        self::assertSame('Events for SightPress.', $composer['description']);
        self::assertSame(['Acme\Events\\' => 'src/'], $composer['autoload']['psr-4']);
        self::assertSame(['Acme\Events\Tests\\' => 'tests/'], $composer['autoload-dev']['psr-4']);

        $manifest = new ManifestReader()->readFile($this->generated.'/composer.json');
        self::assertSame('acme.events', $manifest->id);
        self::assertSame('Acme\Events\EventsPlugin', $manifest->bundleClass);
        self::assertSame(['acme.events.manage' => ['owner', 'admin']], $manifest->capabilities);

        self::assertFileExists($this->generated.'/src/EventsPlugin.php');
        self::assertStringContainsString('namespace Acme\Events;', $this->read('src/EventsPlugin.php'));
        self::assertStringContainsString('translations acme_events --manifest=composer.json', $this->read('composer.json'));
        // Packagist reads plain vX.Y.Z tags, with no component prefix.
        $release = json_decode($this->read('release-please-config.json'), true, 512, \JSON_THROW_ON_ERROR);
        $root = \is_array($release) && \is_array($release['packages'] ?? null) ? $release['packages']['.'] ?? null : null;
        self::assertIsArray($root);
        self::assertSame(['release-type' => 'simple', 'package-name' => 'acme/plugin-events', 'include-v-in-tag' => true, 'include-component-in-tag' => false], array_intersect_key($root, array_flip(['release-type', 'package-name', 'include-v-in-tag', 'include-component-in-tag'])));
        self::assertStringContainsString('package: acme/plugin-events', $this->read('.github/workflows/release.yml'));
        // CI runs the shared plugin workflow, which calls this same composer script.
        self::assertStringContainsString('uses: sightpress/.github/.github/workflows/plugin-qa.yml@main', $this->read('.github/workflows/ci.yml'));
        self::assertStringContainsString('Acme\\\\Events\\\\Contract', $this->read('deptrac.yaml'));
        self::assertStringNotContainsString('bin/setup', $this->read('phpstan.dist.neon'));
        self::assertFileDoesNotExist($this->generated.'/bin/setup');
        self::assertDirectoryDoesNotExist($this->generated.'/tests/Template');

        foreach (Locale::launchCatalogues() as $catalogue) {
            self::assertFileExists(\sprintf('%s/translations/acme_events+intl-icu.%s.xlf', $this->generated, $catalogue));
        }
        self::assertFileDoesNotExist($this->generated.'/translations/template+intl-icu.en.xlf');
    }

    public function testOfficialPluginKeepsItsBareIdAndLeavesNoPlaceholdersBehind(): void
    {
        $this->generate(['--id=image-finder', '--name=ImageFinder']);

        $manifest = new ManifestReader()->readFile($this->generated.'/composer.json');
        self::assertSame('image-finder', $manifest->id);
        self::assertSame('sightpress/plugin-image-finder', $manifest->package);
        self::assertSame('SightPress\Plugin\ImageFinder\ImageFinderPlugin', $manifest->bundleClass);

        foreach ($this->textFiles() as $file) {
            $text = (string) file_get_contents($file);
            self::assertStringNotContainsString('TemplatePlugin', $text, $file);
            self::assertStringNotContainsString('Plugin\Template', $text, $file);
            self::assertStringNotContainsString('Plugin\\\\Template', $text, $file);
            self::assertStringNotContainsString('plugin-template', $text, $file);
        }
    }

    public function testANewPluginStartsItsOwnReleaseHistory(): void
    {
        $this->generate(['--id=image-finder', '--name=ImageFinder']);

        self::assertFileDoesNotExist($this->generated.'/CHANGELOG.md', 'The template\'s changelog is not the new plugin\'s.');
        self::assertSame(['.' => '0.0.0'], json_decode((string) file_get_contents($this->generated.'/.release-please-manifest.json'), true));
    }

    public function testSetupRefusesABareIdForAThirdPartyVendor(): void
    {
        $result = $this->runSetup(['--id=events', '--name=Events', '--vendor=acme']);

        self::assertNotSame(0, $result['status']);
        self::assertStringContainsString('Bare ids are for official plugins', $result['output']);
    }

    public function testGeneratedPluginPassesTheFullBar(): void
    {
        if ('1' !== getenv('SIGHTPRESS_TEMPLATE_FULL_CHECK')) {
            self::markTestSkipped('Set SIGHTPRESS_TEMPLATE_FULL_CHECK=1 to install a generated plugin and run every gate on it.');
        }

        $this->generate(['--id=acme.events', '--name=Events', '--vendor=acme', '--namespace=Acme\Events']);

        $env = ['COMPOSER' => 'composer.dev.json'];
        $steps = [
            ['composer', 'install', '--no-interaction', '--no-progress'],
            ['vendor/bin/phpunit'],
            ['vendor/bin/phpstan', 'analyse', '--memory-limit=1G', '--no-progress'],
            ['vendor/bin/php-cs-fixer', 'fix', '--dry-run', '--diff'],
            ['vendor/bin/rector', 'process', '--dry-run'],
            ['vendor/bin/deptrac', 'analyse', '--no-progress'],
            ['composer', 'audit'],
            ['vendor/bin/sightpress-check-licenses'],
            ['vendor/bin/sightpress-check-translations', 'translations', 'acme_events', '--manifest=composer.json'],
        ];
        foreach ($steps as $step) {
            $result = $this->execute($step, $this->generated, $env);
            self::assertSame(0, $result['status'], \sprintf("%s failed:\n%s", implode(' ', $step), $result['output']));
        }
    }

    /** @param list<string> $arguments */
    private function generate(array $arguments): void
    {
        $result = $this->runSetup($arguments);
        self::assertSame(0, $result['status'], $result['output']);
    }

    /**
     * @param list<string> $arguments
     *
     * @return array{status: int, output: string}
     */
    private function runSetup(array $arguments): array
    {
        return $this->execute([\PHP_BINARY, \dirname(__DIR__, 2).'/bin/setup', '--no-interaction', '--target='.$this->generated, ...$arguments], \dirname(__DIR__, 2), []);
    }

    /**
     * @param list<string>          $command
     * @param array<string, string> $env
     *
     * @return array{status: int, output: string}
     */
    private function execute(array $command, string $cwd, array $env): array
    {
        $process = proc_open(['sh', '-c', 'exec "$@" 2>&1', 'sh', ...$command], [1 => ['pipe', 'w']], $pipes, $cwd, [...getenv(), ...$env]);
        self::assertIsResource($process);
        $output = (string) stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        return ['status' => proc_close($process), 'output' => $output];
    }

    /** @return array{name: string, type: string, description: string, autoload: array{'psr-4': array<string, string>}, 'autoload-dev': array{'psr-4': array<string, string>}} */
    private function composer(): array
    {
        $data = json_decode($this->read('composer.json'), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        /** @var array{name: string, type: string, description: string, autoload: array{'psr-4': array<string, string>}, 'autoload-dev': array{'psr-4': array<string, string>}} $data */
        return $data;
    }

    private function read(string $relative): string
    {
        return (string) file_get_contents($this->generated.'/'.$relative);
    }

    /** @return iterable<string> */
    private function textFiles(): iterable
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->generated, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && !str_contains($file->getPathname(), '/vendor/')) {
                yield $file->getPathname();
            }
        }
    }

    private function remove(string $path): void
    {
        if (is_link($path) || is_file($path)) {
            unlink($path);

            return;
        }
        if (is_dir($path)) {
            foreach (array_diff(scandir($path) ?: [], ['.', '..']) as $child) {
                $this->remove($path.'/'.$child);
            }
            rmdir($path);
        }
    }
}
