<?php

namespace Tests;

use Kdjfs\LoginLegalLinks\BlueprintFactory;
use Kdjfs\LoginLegalLinks\LegalLinks;
use Kirby\Cms\App;
use Kirby\Form\Field;
use Kirby\Toolkit\I18n;

class BlueprintTest extends PluginTestCase
{
    /**
     * Builds the plugin field from the field group, the way the Panel does.
     *
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @return \Kirby\Form\Field Field instance for the site
     */
    protected function field(App $kirby): Field
    {
        $attributes = BlueprintFactory::fieldGroup($kirby);
        $type       = $attributes['type'];
        unset($attributes['type']);

        return new Field($type, $attributes + ['model' => $kirby->site()]);
    }

    public function testFieldGroupIsRegisteredAsBlueprint(): void
    {
        $kirby = $this->kirby();

        $this->assertIsCallable($kirby->extension('blueprints', 'fields/login-legal-links'));
    }

    public function testFieldGroupUsesThePluginType(): void
    {
        $this->assertSame(LegalLinks::FIELD_TYPE, BlueprintFactory::fieldGroup($this->kirby())['type']);
    }

    public function testNeverSavedFieldShowsThreePresetsInPanelLanguage(): void
    {
        $kirby = $this->kirby();
        I18n::$locale = 'de';

        $this->assertSame([
            ['label' => 'Impressum', 'link' => ''],
            ['label' => 'Datenschutz', 'link' => ''],
            ['label' => 'Barrierefreiheit', 'link' => ''],
        ], $this->field($kirby)->emptyValue());
    }

    public function testSavedEmptyListStaysEmpty(): void
    {
        $field = $this->field($this->kirby());
        $field->fill('');

        $this->assertSame([], $field->toFormValue());
    }

    public function testLinkColumnAcceptsPagesAndUrlsOnlyAndIsOptional(): void
    {
        $columns = BlueprintFactory::fieldGroup($this->kirby())['fields'];

        $this->assertSame(['page', 'url'], $columns['link']['options']);
        $this->assertArrayNotHasKey('required', $columns['link']);
        $this->assertTrue($columns['label']['required']);
    }

    public function testNoHelpWithoutConfiguration(): void
    {
        $this->assertArrayNotHasKey('help', BlueprintFactory::fieldGroup($this->kirby()));
    }

    public function testHelpListsConfiguredEntries(): void
    {
        $kirby = $this->kirby([
            'options' => ['kdjfs.login-legal-links' => ['links' => [
                ['label' => 'Impressum', 'link' => 'impressum'],
                ['label' => 'Datenschutz', 'link' => 'https://example.org/privacy'],
            ]]],
        ]);
        I18n::$locale = 'de';

        $this->assertSame(
            'In der Konfiguration hinterlegt: Impressum → impressum, Datenschutz → https&#58;//example.org/privacy. '
            . 'Sobald hier ein Eintrag einen Link hat, gilt nur diese Liste.',
            BlueprintFactory::fieldGroup($kirby)['help']
        );
    }

    /**
     * Kirby runs a field's help through toSafeString() (query templates) and
     * kirbytext() (KirbyTags, Markdown, raw HTML) before the Panel renders it
     * as HTML. Configured values must come out as plain text.
     *
     * @return void
     */
    public function testHelpEscapesQueriesKirbytagsAndHtml(): void
    {
        $kirby = $this->kirby([
            'site'    => ['content' => ['title' => 'GEHEIM']],
            'options' => ['kdjfs.login-legal-links' => ['links' => [
                ['label' => '<b>fett</b> {{ site.title }} (link: https://evil.example) *x* ~~weg~~', 'link' => 'impressum'],
            ]]],
        ]);

        $help = $this->field($kirby)->help();

        $this->assertStringNotContainsString('<b>', $help);
        $this->assertStringNotContainsString('GEHEIM', $help);
        $this->assertStringNotContainsString('<a ', $help);
        $this->assertStringNotContainsString('<em>', $help);
        $this->assertStringNotContainsString('<del>', $help);
        $this->assertStringContainsString('&lt;b&gt;fett&lt;/b&gt;', $help);
    }

    protected function tearDown(): void
    {
        I18n::$locale = 'en';
    }
}
