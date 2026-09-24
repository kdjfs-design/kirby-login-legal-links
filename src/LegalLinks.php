<?php

namespace Kdjfs\LoginLegalLinks;

use Kirby\Cms\App;
use Kirby\Toolkit\I18n;

/**
 * Decides which legal links the Panel login shows and resolves them into
 * label/URL pairs.
 *
 * Two sources exist: the plugin option `links` and a field of the type
 * `login-legal-links` in the site blueprint. The Panel list wins as a whole as
 * soon as one of its entries has a link. Invalid entries are dropped and
 * reported as warnings instead of throwing – the login must never break.
 */
final class LegalLinks
{
    /**
     * Field type registered by the plugin; the site field is found by it.
     */
    public const FIELD_TYPE = 'login-legal-links';

    /**
     * Schemes an external link may use.
     */
    private const ALLOWED_SCHEMES = ['http', 'https'];

    /**
     * Messages about dropped entries, in the order they occurred.
     *
     * @var list<string>
     */
    private array $warnings = [];

    /**
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @param string $locale Panel language code used for labels and warnings
     */
    public function __construct(
        private readonly App $kirby,
        private readonly string $locale
    ) {
    }

    /**
     * Resolves the links to show on the login.
     *
     * @return array{links: list<array{label: string, url: string}>, warnings: list<string>}
     */
    public function resolve(): array
    {
        $this->warnings = [];
        $links          = [];

        foreach ($this->entries() as $index => $entry) {
            $link = $this->resolveEntry($entry, $index + 1);

            if ($link !== null) {
                $links[] = $link;
            }
        }

        if ($links === [] && ($this->warnings === [] || $this->hasOnlyConfigWarning() === true)) {
            $this->warn('none');
        }

        return ['links' => $links, 'warnings' => $this->warnings];
    }

    /**
     * Returns the valid entries of the configuration with localised labels and
     * unresolved links, for the help text of the Panel field.
     *
     * @return list<array{label: string, link: string}>
     */
    public function configuredEntries(): array
    {
        $entries = [];

        foreach ($this->configRows() as $row) {
            if (is_array($row) === false) {
                continue;
            }

            $label = $this->label($row['label'] ?? null);
            $link  = $this->linkValue($row['link'] ?? null);

            if ($label !== '' && $link !== '') {
                $entries[] = ['label' => $label, 'link' => $link];
            }
        }

        return $entries;
    }

    /**
     * Whether the links open in a new tab.
     *
     * @return bool
     */
    public function newTab(): bool
    {
        return $this->kirby->option('kdjfs.login-legal-links.newTab', false) === true;
    }

    /**
     * Returns the entries of the source in charge: the Panel list if one of
     * its rows has a link, otherwise the configuration.
     *
     * @return list<mixed> Raw entries, validated one by one in resolveEntry()
     */
    private function entries(): array
    {
        $panelRows = $this->panelRows();

        foreach ($panelRows as $row) {
            if (is_array($row) === true && $this->linkValue($row['link'] ?? null) !== '') {
                return $panelRows;
            }
        }

        return $this->configRows();
    }

    /**
     * Reads the rows of the first field of the plugin's type in the site
     * blueprint. The field name is up to the developer.
     *
     * @return list<mixed> Stored rows, empty if there is no such field or it was never saved
     */
    private function panelRows(): array
    {
        $site = $this->kirby->site();

        foreach ($site->blueprint()->fields() as $name => $definition) {
            if (($definition['type'] ?? null) !== self::FIELD_TYPE) {
                continue;
            }

            $languageCode = $this->kirby->multilang() === true ? $this->siteLanguageCode() : null;

            return array_values($site->content($languageCode)->get($name)->yaml());
        }

        return [];
    }

    /**
     * Returns the configured entries, or an empty list with a warning when the
     * option is not a list.
     *
     * @return list<mixed>
     */
    private function configRows(): array
    {
        $rows = $this->kirby->option('kdjfs.login-legal-links.links', []);

        if (is_array($rows) === false) {
            $this->warn('config');

            return [];
        }

        return array_values($rows);
    }

    /**
     * Turns one raw entry into a label/URL pair or drops it with a warning.
     *
     * @param mixed $entry Raw entry from the configuration or the Panel
     * @param int $position 1-based position for the warning text
     * @return array{label: string, url: string}|null Null if the entry is dropped
     */
    private function resolveEntry(mixed $entry, int $position): array|null
    {
        if (is_array($entry) === false) {
            $this->warn('entry', ['position' => $position]);

            return null;
        }

        $label = $this->label($entry['label'] ?? null);

        if ($label === '') {
            $this->warn('label', ['position' => $position]);

            return null;
        }

        $link = $this->linkValue($entry['link'] ?? null);

        if ($link === '') {
            $this->warn('link', ['label' => $label]);

            return null;
        }

        $url = $this->url($link, $label);

        return $url === null ? null : ['label' => $label, 'url' => $url];
    }

    /**
     * Resolves a link value into an absolute URL.
     *
     * Values with a scheme other than `page` are external links and must use
     * http or https. Everything else is looked up as page id or UUID.
     *
     * @param string $link Trimmed link value
     * @param string $label Label of the entry, for warnings
     * @return string|null Absolute URL, or null if the entry is dropped
     */
    private function url(string $link, string $label): string|null
    {
        $parts  = parse_url($link);
        $scheme = is_array($parts) === true && isset($parts['scheme']) === true
            ? strtolower($parts['scheme'])
            : null;

        if ($parts === false || ($scheme === null && isset($parts['host']) === true)) {
            $this->warn('scheme', ['label' => $label]);

            return null;
        }

        if ($scheme !== null && $scheme !== 'page') {
            if (in_array($scheme, self::ALLOWED_SCHEMES, true) === false || empty($parts['host']) === true) {
                $this->warn('scheme', ['label' => $label]);

                return null;
            }

            return $link;
        }

        $page = $this->kirby->page($link);

        if ($page === null) {
            $this->warn('page', ['label' => $label, 'link' => $link]);

            return null;
        }

        if ($page->isDraft() === true) {
            $this->warn('draft', ['label' => $label, 'link' => $link]);

            return null;
        }

        return $this->kirby->multilang() === true
            ? $page->url($this->siteLanguageCode())
            : $page->url();
    }

    /**
     * Picks the label in the Panel language.
     *
     * A string is used as it is. For an array: the Panel language, then
     * English, then the first non-empty entry.
     *
     * @param mixed $label Raw label
     * @return string Trimmed label, empty if none is usable
     */
    private function label(mixed $label): string
    {
        if (is_string($label) === true) {
            return trim($label);
        }

        if (is_array($label) === false) {
            return '';
        }

        foreach ([$this->locale, 'en'] as $code) {
            if (is_string($label[$code] ?? null) === true && trim($label[$code]) !== '') {
                return trim($label[$code]);
            }
        }

        foreach ($label as $candidate) {
            if (is_string($candidate) === true && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return '';
    }

    /**
     * Normalises a raw link value.
     *
     * @param mixed $link Raw link
     * @return string Trimmed link, empty if it is not a string
     */
    private function linkValue(mixed $link): string
    {
        return is_string($link) === true ? trim($link) : '';
    }

    /**
     * Returns the site language matching the Panel language, or the default
     * language if the site has no such language.
     *
     * @return string Language code
     */
    private function siteLanguageCode(): string
    {
        return $this->kirby->language($this->locale)?->code()
            ?? $this->kirby->defaultLanguage()->code();
    }

    /**
     * Whether the only warning so far is the one about an invalid option.
     *
     * @return bool
     */
    private function hasOnlyConfigWarning(): bool
    {
        return $this->warnings === [$this->message('config')];
    }

    /**
     * Records a warning.
     *
     * @param string $key Key below kdjfs.login-legal-links.warning.
     * @param array $data Placeholder values
     * @return void
     */
    private function warn(string $key, array $data = []): void
    {
        $this->warnings[] = $this->message($key, $data);
    }

    /**
     * Builds a warning text in the Panel language.
     *
     * @param string $key Key below kdjfs.login-legal-links.warning.
     * @param array $data Placeholder values
     * @return string Warning text
     */
    private function message(string $key, array $data = []): string
    {
        return I18n::template('kdjfs.login-legal-links.warning.' . $key, null, $data, $this->locale);
    }
}
