<?php

declare(strict_types=1);

use Nette\Utils\DateTime;
use Nette\Utils\Strings;

require_once __DIR__ . '/vendor/autoload.php';

// remove phpstan, because it is already prefixed in its own scope
$dateTime = DateTime::from('now');
$timestamp = $dateTime->format('Ymd');

// see https://github.com/humbug/php-scoper
return [
    'prefix' => 'RevealPrefix' . $timestamp,
    // @see https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#exposed-symbols - regex format
    'exclude-namespaces' => [
        '#^PHPStan#',
        '#^Reveal#',
        '#^PhpParser#',
        '#^Symplify\\\\Astral#',
        '#^Symplify\\\\SmartFileSystem#',
        '#^Symplify\\\\PackageBuilder#',
    ],
    'expose-functions' => ['u', 'b', 's', 'trigger_deprecation'],
    'patchers' => [
        // fixes https://github.com/rectorphp/rector/issues/7017 - still needed 2022-06
        function (string $filePath, string $prefix, string $content): string {
            if (\str_ends_with($filePath, 'vendor/symfony/string/ByteString.php')) {
                return Strings::replace($content, '#' . $prefix . '\\\\\\\\1_\\\\\\\\2#', '\\\\1_\\\\2');
            }

            if (\str_ends_with($filePath, 'vendor/symfony/string/AbstractUnicodeString.php')) {
                return Strings::replace($content, '#' . $prefix . '\\\\\\\\1_\\\\\\\\2#', '\\\\1_\\\\2');
            }

            return $content;
        },

        // scoper missed PSR-4 autodiscovery in Symfony - still needed
        function (string $filePath, string $prefix, string $content): string {
            if (! \str_ends_with($filePath, 'config.php') && ! \str_ends_with($filePath, 'services.php')) {
                return $content;
            }

            // skip "Rector\\" namespace
            if (\str_contains($content, '$services->load(\'Reveal')) {
                return $content;
            }

            return Strings::replace($content, '#services\->load\(\'#', "services->load('" . $prefix . '\\');
        },

        // latte services
        function (string $filePath, string $prefix, string $content): string {
            if (! \str_ends_with($filePath, 'config.neon') && ! \str_ends_with($filePath, 'services.neon')) {
                return $content;
            }

            return Strings::replace($content, '#- Latte\\\\#', "- $prefix\\Latte\\");
        },
    ],
];
