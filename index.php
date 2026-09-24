<?php

use Kirby\Filesystem\F;

/**
 * Classes are loaded through Kirby, not through Composer. That way the plugin
 * also works when it is simply copied into site/plugins/ without Composer.
 * With a Composer installation the PSR-4 autoloader takes precedence and this
 * registration costs nothing.
 */
F::loadClasses([
    'kdjfs\\loginlegallinks\\legallinks' => __DIR__ . '/src/LegalLinks.php',
]);

/**
 * Registration only. Every part lives in its own file.
 *
 * `require` rather than `require_once`: the latter returns `true` instead of
 * the array once a file has already been included, which would turn an
 * extension into a boolean without any error.
 */
Kirby::plugin('kdjfs/login-legal-links', [
    'options' => [
        'links'  => [],
        'newTab' => false,
    ],
    'fields' => require __DIR__ . '/plugin/fields.php',
    'translations' => [
        'en' => require __DIR__ . '/translations/en.php',
        'de' => require __DIR__ . '/translations/de.php',
    ],
]);
