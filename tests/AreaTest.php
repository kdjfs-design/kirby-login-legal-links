<?php

namespace Tests;

use Kirby\Cms\App;

class AreaTest extends PluginTestCase
{
    /**
     * Calls the login view action of the plugin's area extension.
     *
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @return mixed Whatever the action returns
     */
    protected function pluginView(App $kirby): mixed
    {
        $area = $kirby->plugin('kdjfs/login-legal-links')->extends()['areas']['login'];

        return $area($kirby)['views']['login']['action']();
    }

    /**
     * Early warning for Kirby updates: the wrapper relies on the core login
     * action returning component and props. If this fails after an update,
     * read kirby/config/areas/login.php again before anything else.
     *
     * @return void
     */
    public function testCoreLoginActionStillHasTheExpectedShape(): void
    {
        $view = $this->kirby()->core()->area('login')['views']['login']['action']();

        $this->assertSame('k-login-view', $view['component']);
        $this->assertIsArray($view['props']);
        $this->assertArrayHasKey('methods', $view['props']);
        $this->assertArrayHasKey('pending', $view['props']);
    }

    public function testWrapperKeepsAllOriginalPropsAndAddsItsOwn(): void
    {
        $kirby = $this->kirby([
            'options' => ['kdjfs.login-legal-links' => [
                'links'  => [['label' => 'Impressum', 'link' => 'impressum']],
                'newTab' => true,
            ]],
        ]);

        $original = $kirby->core()->area('login')['views']['login']['action']();
        $wrapped  = $this->pluginView($kirby);

        $this->assertSame($original['component'], $wrapped['component']);

        foreach ($original['props'] as $key => $value) {
            $this->assertSame($value, $wrapped['props'][$key], 'Prop ' . $key . ' changed');
        }

        $this->assertSame([['label' => 'Impressum', 'url' => 'https://example.com/impressum']], $wrapped['props']['legalLinks']);
        $this->assertTrue($wrapped['props']['legalLinksNewTab']);
    }

    public function testWarningsOnlyReachThePanelInDebugMode(): void
    {
        $quiet = $this->pluginView($this->kirby());
        $debug = $this->pluginView($this->kirby(['options' => ['debug' => true]]));

        $this->assertSame([], $quiet['props']['legalLinksWarnings']);
        $this->assertNotEmpty($debug['props']['legalLinksWarnings']);
    }

    public function testWarningsUseThePanelLanguage(): void
    {
        $view = $this->pluginView($this->kirby(['options' => ['debug' => true, 'panel' => ['language' => 'de']]]));

        $this->assertSame(
            ['Keine Rechtslinks gesetzt, weder in der Konfiguration noch im Panel.'],
            $view['props']['legalLinksWarnings']
        );
    }

    /**
     * The login must never break because of this plugin. Malformed YAML in
     * the site field makes the resolution throw when it is read; the view
     * has to come back exactly as the core built it.
     *
     * A resolver that throws directly on `$kirby->site()->blueprint()`
     * cannot be used here: Kirby resolves every core area, including `site`,
     * as soon as any single area is requested (Cms/Loader.php, areas()), so
     * the exception would already surface while building $original instead
     * of inside the plugin's try block. The `yaml.handler` option switches
     * the parser used for `Field::yaml()` to Symfony's, which throws a
     * `ParseException` on invalid YAML – Spyc, the default, repairs almost
     * anything silently.
     *
     * @return void
     */
    public function testBrokenResolutionLeavesTheViewUntouched(): void
    {
        $kirby = $this->kirby([
            'options' => ['yaml.handler' => 'symfony'],
            'blueprints' => ['site' => [
                'fields' => ['legal' => ['type' => 'login-legal-links']],
            ]],
            'site' => [
                'content' => ['legal' => "a: [1, 2\nb: unterminated \"quote"],
            ],
        ]);

        $original = $kirby->core()->area('login')['views']['login']['action']();

        $this->assertSame($original, $this->pluginView($kirby));
    }
}
