<?php

namespace Tests;

use Kirby\Toolkit\I18n;

class TranslationsTest extends PluginTestCase
{
    /**
     * Returns the translation sets the plugin registers, keyed by locale.
     *
     * @return array Locale code => key/value pairs
     */
    protected function pluginTranslations(): array
    {
        return array_map(
            fn ($set) => array_filter(
                $set,
                fn ($key) => str_starts_with($key, 'kdjfs.login-legal-links.'),
                ARRAY_FILTER_USE_KEY
            ),
            $this->kirby()->extensions('translations')
        );
    }

    public function testEnglishAndGermanAreRegistered(): void
    {
        $sets = $this->pluginTranslations();

        $this->assertNotEmpty($sets['en'] ?? []);
        $this->assertNotEmpty($sets['de'] ?? []);
    }

    /**
     * A locale missing a key makes panel.t() return undefined and the text
     * render empty. Comparing the sets is the only way to notice.
     *
     * @return void
     */
    public function testEveryLocaleDefinesTheSameKeys(): void
    {
        $sets     = $this->pluginTranslations();
        $expected = array_keys($sets['en']);
        sort($expected);

        foreach ($sets as $locale => $set) {
            $keys = array_keys($set);
            sort($keys);

            $this->assertSame($expected, $keys, 'Locale ' . $locale . ' differs from en');
        }
    }

    public function testKeysResolveThroughKirby(): void
    {
        $this->kirby();

        $this->assertSame('Impressum', I18n::translate('kdjfs.login-legal-links.default.imprint', null, 'de'));
        $this->assertSame('Imprint', I18n::translate('kdjfs.login-legal-links.default.imprint', null, 'en'));
    }
}
