<?php

declare(strict_types=1);

namespace Phpcq\PhpunitPluginTest;

use Phpcq\PluginApi\Version10\DiagnosticsPluginInterface;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing()]
final class PluginManifestTest extends TestCase
{
    private const ROOT = __DIR__ . '/..';

    public function testManifestNameMatchesPluginName(): void
    {
        /** @var DiagnosticsPluginInterface $plugin */
        $plugin = include self::ROOT . '/src/phpunit.php';

        self::assertSame($plugin->getName(), $this->loadManifest()['name']);
    }

    public function testManifestPointsToPluginFile(): void
    {
        $manifest = $this->loadManifest();

        self::assertSame('src/phpunit.php', $manifest['url']);
        self::assertFileExists(self::ROOT . '/' . $manifest['url']);
    }

    /** @return iterable<string, array{0: string}> */
    public static function supportedMajorVersionsProvider(): iterable
    {
        foreach (['6', '7', '8', '9', '10', '11', '12', '13'] as $major) {
            yield 'phpunit ' . $major => [$major];
        }
    }

    #[DataProvider('supportedMajorVersionsProvider')]
    public function testManifestAllowsPhpunitMajorVersion(string $major): void
    {
        $constraints = array_map(
            'trim',
            explode('||', $this->loadManifest()['requirements']['tool']['phpunit']['constraints'])
        );

        self::assertContains('^' . $major . '.0', $constraints);
    }

    /**
     * @return array{
     *     name: string,
     *     url: string,
     *     requirements: array{tool: array{phpunit: array{constraints: string}}}
     * }
     */
    private function loadManifest(): array
    {
        /**
         * @var array{
         *     name: string,
         *     url: string,
         *     requirements: array{tool: array{phpunit: array{constraints: string}}}
         * } $data
         */
        $data = json_decode(
            (string) file_get_contents(self::ROOT . '/phpcq-plugin.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return $data;
    }
}
