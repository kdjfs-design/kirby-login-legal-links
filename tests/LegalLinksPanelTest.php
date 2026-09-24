<?php

namespace Tests;

use Kdjfs\LoginLegalLinks\LegalLinks;
use Kirby\Cms\App;
use Kirby\Data\Yaml;

class LegalLinksPanelTest extends PluginTestCase
{
    /**
     * Creates a single-language app with a site blueprint containing the
     * plugin field under a custom name, and the given stored rows.
     *
     * @param array|null $rows Rows stored in the site content, null for a field that was never saved
     * @param array $configLinks Value of the option kdjfs.login-legal-links.links
     * @return \Kirby\Cms\App Kirby instance
     */
    protected function kirbyWithPanelRows(array|null $rows, array $configLinks = []): App
    {
        $content = $rows === null ? [] : ['myLegalLinks' => Yaml::encode($rows)];

        return $this->kirby([
            'options'    => ['kdjfs.login-legal-links' => ['links' => $configLinks]],
            'blueprints' => [
                'site' => [
                    'fields' => [
                        'myLegalLinks' => ['type' => LegalLinks::FIELD_TYPE],
                    ],
                ],
            ],
            'site' => ['content' => $content],
        ]);
    }

    public function testFieldIsFoundByTypeNotByName(): void
    {
        $kirby = $this->kirbyWithPanelRows([['label' => 'Impressum', 'link' => 'page://imprint-uuid']]);

        $this->assertSame(
            [['label' => 'Impressum', 'url' => 'https://example.com/impressum']],
            (new LegalLinks($kirby, 'de'))->resolve()['links']
        );
    }

    public function testPanelListReplacesTheWholeConfiguration(): void
    {
        $kirby = $this->kirbyWithPanelRows(
            [['label' => 'Aus dem Panel', 'link' => 'https://example.org/panel']],
            [
                ['label' => 'Aus der Konfiguration A', 'link' => 'impressum'],
                ['label' => 'Aus der Konfiguration B', 'link' => 'datenschutz'],
            ]
        );

        $this->assertSame(
            ['Aus dem Panel'],
            array_column((new LegalLinks($kirby, 'de'))->resolve()['links'], 'label')
        );
    }

    /**
     * The three preset rows carry labels but no links. Saved like that, they
     * must not count as "set" – otherwise they would silently switch off the
     * configuration and the login would show nothing.
     *
     * @return void
     */
    public function testPanelRowsWithoutLinkLeaveTheConfigurationInCharge(): void
    {
        $kirby = $this->kirbyWithPanelRows(
            [
                ['label' => 'Impressum', 'link' => ''],
                ['label' => 'Datenschutz', 'link' => ''],
                ['label' => 'Barrierefreiheit', 'link' => ''],
            ],
            [['label' => 'Aus der Konfiguration', 'link' => 'impressum']]
        );

        $result = (new LegalLinks($kirby, 'de'))->resolve();

        $this->assertSame(['Aus der Konfiguration'], array_column($result['links'], 'label'));
        $this->assertSame([], $result['warnings']);
    }

    public function testNeverSavedFieldLeavesTheConfigurationInCharge(): void
    {
        $kirby = $this->kirbyWithPanelRows(null, [['label' => 'Aus der Konfiguration', 'link' => 'impressum']]);

        $this->assertSame(
            ['Aus der Konfiguration'],
            array_column((new LegalLinks($kirby, 'de'))->resolve()['links'], 'label')
        );
    }

    public function testPanelRowsWithoutLinkAreSkippedOnceTheListIsInCharge(): void
    {
        $kirby = $this->kirbyWithPanelRows([
            ['label' => 'Impressum', 'link' => 'page://imprint-uuid'],
            ['label' => 'Barrierefreiheit', 'link' => ''],
        ]);

        $result = (new LegalLinks($kirby, 'de'))->resolve();

        $this->assertSame(['Impressum'], array_column($result['links'], 'label'));
        $this->assertSame(['Eintrag „Barrierefreiheit“ hat keinen Link und wird übersprungen.'], $result['warnings']);
    }

    public function testPanelRowWithJavascriptLinkIsDropped(): void
    {
        $kirby = $this->kirbyWithPanelRows([
            ['label' => 'Impressum', 'link' => 'page://imprint-uuid'],
            ['label' => 'Böse', 'link' => 'javascript:alert(1)'],
        ]);

        $this->assertSame(
            ['Impressum'],
            array_column((new LegalLinks($kirby, 'de'))->resolve()['links'], 'label')
        );
    }

    public function testWithoutPluginFieldInBlueprintTheConfigurationApplies(): void
    {
        $kirby = $this->kirby([
            'options' => ['kdjfs.login-legal-links' => ['links' => [['label' => 'Impressum', 'link' => 'impressum']]]],
        ]);

        $this->assertCount(1, (new LegalLinks($kirby, 'de'))->resolve()['links']);
    }

    /**
     * Multi-language site: labels come from the site language matching the
     * Panel language, page URLs point to that language. A Panel language the
     * site does not have falls back to the default language.
     *
     * @return void
     */
    public function testMultiLanguageSiteUsesMatchingLanguage(): void
    {
        $kirby = $this->kirby([
            'languages' => [
                ['code' => 'de', 'default' => true, 'url' => '/'],
                ['code' => 'en', 'url' => '/en'],
            ],
            'blueprints' => [
                'site' => ['fields' => ['legal' => ['type' => LegalLinks::FIELD_TYPE]]],
            ],
            'site' => [
                'translations' => [
                    [
                        'code'    => 'de',
                        'content' => ['legal' => Yaml::encode([['label' => 'Impressum', 'link' => 'page://imprint-uuid']])],
                    ],
                    [
                        'code'    => 'en',
                        'content' => ['legal' => Yaml::encode([['label' => 'Imprint', 'link' => 'page://imprint-uuid']])],
                    ],
                ],
            ],
        ]);

        $imprint = $kirby->page('impressum');

        $this->assertSame(
            [['label' => 'Imprint', 'url' => $imprint->url('en')]],
            (new LegalLinks($kirby, 'en'))->resolve()['links']
        );
        $this->assertSame(
            [['label' => 'Impressum', 'url' => $imprint->url('de')]],
            (new LegalLinks($kirby, 'de'))->resolve()['links']
        );
        $this->assertSame(
            [['label' => 'Impressum', 'url' => $imprint->url('de')]],
            (new LegalLinks($kirby, 'fr'))->resolve()['links']
        );
    }
}
