<?php

use Kdjfs\LoginLegalLinks\BlueprintFactory;
use Kirby\Filesystem\F;

/**
 * Classes are loaded through Kirby, not through Composer. That way the plugin
 * also works when it is simply copied into site/plugins/ without Composer.
 * With a Composer installation the PSR-4 autoloader takes precedence and this
 * registration costs nothing.
 */
F::loadClasses([
    'kdjfs\\loginlegallinks\\legallinks' => __DIR__ . '/src/LegalLinks.php',
    'kdjfs\\loginlegallinks\\blueprintfactory' => __DIR__ . '/src/BlueprintFactory.php',
    'kdjfs\\loginlegallinks\\loginview' => __DIR__ . '/src/LoginView.php',
]);

/**
 * Registration only. Every part lives in its own file.
 *
 * `require` rather than `require_once`: the latter returns `true` instead of
 * the array once a file has already been included, which would turn an
 * extension into a boolean without any error.
 *
 * `version` is what the Panel's update check compares against when the plugin
 * was copied or downloaded rather than installed with Composer. It is set by
 * the release script, never by hand; CI fails when a tag disagrees with it.
 */
Kirby::plugin('kdjfs/login-legal-links', [
    'options' => [
        'links'  => [],
        'newTab' => false,
    ],
    'fields' => require __DIR__ . '/plugin/fields.php',
    'blueprints' => [
        'fields/login-legal-links' => fn ($kirby) => BlueprintFactory::fieldGroup($kirby),
    ],
    'areas' => require __DIR__ . '/plugin/areas.php',
    'translations' => [
        'en' => require __DIR__ . '/translations/en.php',
        'de' => require __DIR__ . '/translations/de.php',
    ],
], version: '1.0.0');
