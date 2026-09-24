<?php

namespace Tests;

use Kdjfs\LoginLegalLinks\LegalLinks;
use Kirby\Cms\App;

class LegalLinksConfigTest extends PluginTestCase
{
    /**
     * Creates an app whose configuration holds the given link entries.
     *
     * @param mixed $links Value of the option kdjfs.login-legal-links.links
     * @param array $options Further plugin options
     * @return \Kirby\Cms\App Kirby instance
     */
    protected function kirbyWithLinks(mixed $links, array $options = []): App
    {
        return $this->kirby([
            'options' => ['kdjfs.login-legal-links' => ['links' => $links] + $options],
        ]);
    }

    public function testPageIdResolvesToAbsoluteUrl(): void
    {
        $kirby  = $this->kirbyWithLinks([['label' => 'Impressum', 'link' => 'impressum']]);
        $result = (new LegalLinks($kirby, 'de'))->resolve();

        $this->assertSame([['label' => 'Impressum', 'url' => 'https://example.com/impressum']], $result['links']);
        $this->assertSame([], $result['warnings']);
    }

    public function testPageUuidResolvesToAbsoluteUrl(): void
    {
        $kirby = $this->kirbyWithLinks([['label' => 'Datenschutz', 'link' => 'page://privacy-uuid']]);

        $this->assertSame(
            [['label' => 'Datenschutz', 'url' => 'https://example.com/datenschutz']],
            (new LegalLinks($kirby, 'de'))->resolve()['links']
        );
    }

    public function testExternalHttpsUrlIsKept(): void
    {
        $kirby = $this->kirbyWithLinks([['label' => 'Barrierefreiheit', 'link' => 'https://example.org/a11y']]);

        $this->assertSame(
            [['label' => 'Barrierefreiheit', 'url' => 'https://example.org/a11y']],
            (new LegalLinks($kirby, 'de'))->resolve()['links']
        );
    }

    public function testOrderOfEntriesIsKept(): void
    {
        $kirby = $this->kirbyWithLinks([
            ['label' => 'B', 'link' => 'datenschutz'],
            ['label' => 'A', 'link' => 'impressum'],
        ]);

        $labels = array_column((new LegalLinks($kirby, 'de'))->resolve()['links'], 'label');

        $this->assertSame(['B', 'A'], $labels);
    }

    /**
     * Only http and https may reach the href. Everything else – including
     * spellings that browsers still execute – has to be dropped.
     *
     * @return void
     */
    public function testDangerousSchemesAreDropped(): void
    {
        $kirby = $this->kirbyWithLinks([
            ['label' => 'a', 'link' => 'javascript:alert(1)'],
            ['label' => 'b', 'link' => 'JavaScript:alert(1)'],
            ['label' => 'c', 'link' => '  javascript:alert(1)'],
            ['label' => 'd', 'link' => 'data:text/html,<script>alert(1)</script>'],
            ['label' => 'e', 'link' => 'mailto:info@example.com'],
            ['label' => 'f', 'link' => '//evil.example/'],
            ['label' => 'g', 'link' => 'https://'],
        ]);

        $result = (new LegalLinks($kirby, 'de'))->resolve();

        $this->assertSame([], $result['links']);
        $this->assertCount(7, $result['warnings']);
    }

    public function testMissingPageIsDroppedWithWarning(): void
    {
        $kirby  = $this->kirbyWithLinks([['label' => 'Impressum', 'link' => 'gibt-es-nicht']]);
        $result = (new LegalLinks($kirby, 'de'))->resolve();

        $this->assertSame([], $result['links']);
        $this->assertSame(['Eintrag „Impressum“ übersprungen – Seite „gibt-es-nicht“ nicht gefunden.'], $result['warnings']);
    }

    /**
     * $kirby->page() finds drafts by id and by UUID. A draft cannot be opened
     * by visitors, so the link would lead to an error page.
     *
     * @return void
     */
    public function testDraftIsDroppedAlsoViaUuid(): void
    {
        $kirby = $this->kirbyWithLinks([
            ['label' => 'Per ID', 'link' => 'entwurf'],
            ['label' => 'Per UUID', 'link' => 'page://draft-uuid'],
        ]);

        $result = (new LegalLinks($kirby, 'de'))->resolve();

        $this->assertSame([], $result['links']);
        $this->assertSame([
            'Eintrag „Per ID“ übersprungen – Seite „entwurf“ ist ein Entwurf.',
            'Eintrag „Per UUID“ übersprungen – Seite „page://draft-uuid“ ist ein Entwurf.',
        ], $result['warnings']);
    }

    public function testEntriesWithoutLabelOrLinkAreDropped(): void
    {
        $kirby = $this->kirbyWithLinks([
            ['label' => '', 'link' => 'impressum'],
            ['link' => 'impressum'],
            ['label' => 'Ohne Link'],
            ['label' => 'Leerer Link', 'link' => '   '],
        ]);

        $result = (new LegalLinks($kirby, 'de'))->resolve();

        $this->assertSame([], $result['links']);
        $this->assertSame([
            'Eintrag 1 hat keine Beschriftung und wird übersprungen.',
            'Eintrag 2 hat keine Beschriftung und wird übersprungen.',
            'Eintrag „Ohne Link“ hat keinen Link und wird übersprungen.',
            'Eintrag „Leerer Link“ hat keinen Link und wird übersprungen.',
        ], $result['warnings']);
    }

    public function testInvalidConfigurationIsIgnoredWithWarning(): void
    {
        $kirby  = $this->kirbyWithLinks('impressum');
        $result = (new LegalLinks($kirby, 'de'))->resolve();

        $this->assertSame([], $result['links']);
        $this->assertSame([
            'Die Option kdjfs.login-legal-links.links ist keine Liste und wird ignoriert.',
            'Keine Rechtslinks gesetzt, weder in der Konfiguration noch im Panel.',
        ], $result['warnings']);
    }

    public function testInvalidEntryIsSkippedWithWarning(): void
    {
        $kirby  = $this->kirbyWithLinks(['impressum', ['label' => 'Impressum', 'link' => 'impressum']]);
        $result = (new LegalLinks($kirby, 'de'))->resolve();

        $this->assertCount(1, $result['links']);
        $this->assertSame(
            ['Eintrag 1 ist keine Liste aus Beschriftung und Link und wird übersprungen.'],
            $result['warnings']
        );
    }

    public function testNothingSetGivesNoLinksAndOneWarning(): void
    {
        $result = (new LegalLinks($this->kirby(), 'en'))->resolve();

        $this->assertSame([], $result['links']);
        $this->assertSame(['No legal links set, neither in the configuration nor in the Panel.'], $result['warnings']);
    }

    public function testLabelArrayUsesLocaleThenEnglishThenFirstEntry(): void
    {
        $kirby = $this->kirbyWithLinks([
            ['label' => ['de' => 'Datenschutz', 'en' => 'Privacy'], 'link' => 'datenschutz'],
            ['label' => ['fr' => 'Mentions', 'en' => 'Imprint'], 'link' => 'impressum'],
            ['label' => ['fr' => 'Accessibilité', 'it' => 'Accessibilità'], 'link' => 'impressum'],
        ]);

        $labels = array_column((new LegalLinks($kirby, 'de'))->resolve()['links'], 'label');

        $this->assertSame(['Datenschutz', 'Imprint', 'Accessibilité'], $labels);
    }

    public function testConfiguredEntriesReturnLocalisedLabelsAndRawLinks(): void
    {
        $kirby = $this->kirbyWithLinks([
            ['label' => ['de' => 'Datenschutz', 'en' => 'Privacy'], 'link' => 'page://privacy-uuid'],
            'kaputt',
            ['label' => '', 'link' => 'impressum'],
        ]);

        $this->assertSame(
            [['label' => 'Privacy', 'link' => 'page://privacy-uuid']],
            (new LegalLinks($kirby, 'en'))->configuredEntries()
        );
    }

    public function testNewTabFollowsTheOption(): void
    {
        $this->assertFalse((new LegalLinks($this->kirby(), 'de'))->newTab());
        $this->assertTrue((new LegalLinks($this->kirbyWithLinks([], ['newTab' => true]), 'de'))->newTab());
    }
}
