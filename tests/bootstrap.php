<?php

/**
 * Test bootstrap for both layouts the plugin lives in.
 *
 * Standalone repository: Composer puts the autoloader into vendor/ and Kirby
 * into kirby/. Inside a Kirby project: the plugin sits in site/plugins/, so
 * both live four levels up. Whichever exists is used.
 */

/**
 * Requires the first file of a list that exists.
 *
 * @param array $candidates Paths in order of preference
 * @param string $what Name of the file for the error message
 * @return void
 * @throws \RuntimeException If none of the paths exists
 */
function legalLinksRequireFirst(array $candidates, string $what): void
{
    foreach ($candidates as $candidate) {
        if (file_exists($candidate) === true) {
            require_once $candidate;

            return;
        }
    }

    throw new RuntimeException(
        $what . ' not found – run `composer install`. Looked in: ' . implode(', ', $candidates)
    );
}

legalLinksRequireFirst([
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../../vendor/autoload.php',
], 'Composer autoloader');

/**
 * getkirby/cms is of type kirby-cms; the composer installer puts it into
 * kirby/ in the root rather than into vendor/.
 */
legalLinksRequireFirst([
    __DIR__ . '/../kirby/bootstrap.php',
    __DIR__ . '/../vendor/getkirby/cms/bootstrap.php',
    __DIR__ . '/../../../../kirby/bootstrap.php',
], 'Kirby bootstrap');

/**
 * Kirby installs Whoops as error handler as soon as an app is constructed;
 * PHPUnit would report every test as risky. `$enableWhoops` is Kirby's own
 * switch for exactly this.
 */
Kirby\Cms\App::$enableWhoops = false;

/**
 * Registers the plugin once, statically. The app instances in the tests use a
 * fixture root without site/plugins, because a second registration throws a
 * DuplicateException.
 */
require_once __DIR__ . '/../index.php';

// Base class of the tests, for which there is no autoloader
require_once __DIR__ . '/PluginTestCase.php';
