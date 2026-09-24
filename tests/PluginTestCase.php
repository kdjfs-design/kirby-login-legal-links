<?php

namespace Tests;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use PHPUnit\Framework\TestCase;

/**
 * Base for tests that need a complete Kirby instance.
 */
abstract class PluginTestCase extends TestCase
{
    /**
     * Index root for every app instance in the tests.
     *
     * Empty on purpose: a root with a site/plugins folder would register the
     * plugin a second time.
     */
    protected const FIXTURE_ROOT = __DIR__ . '/fixtures';

    /**
     * Creates a Kirby instance with an absolute base URL and three pages:
     * a listed imprint, an unlisted privacy page and a draft.
     *
     * @param array $props Additional or overriding app properties
     * @return \Kirby\Cms\App Kirby instance
     */
    protected function kirby(array $props = []): App
    {
        // Blueprint::find() caches every loaded blueprint in a static array
        // that a new App does not reset (Cms/Blueprint.php). Without this, a
        // test would get the site blueprint of an earlier test.
        Blueprint::$loaded = [];

        return new App(array_replace_recursive([
            'roots' => ['index' => static::FIXTURE_ROOT],
            'urls'  => ['index' => 'https://example.com'],
            'site'  => [
                'children' => [
                    ['slug' => 'impressum', 'num' => 1, 'content' => ['uuid' => 'imprint-uuid']],
                    ['slug' => 'datenschutz', 'content' => ['uuid' => 'privacy-uuid']],
                ],
                'drafts' => [
                    ['slug' => 'entwurf', 'content' => ['uuid' => 'draft-uuid']],
                ],
            ],
        ], $props));
    }
}
