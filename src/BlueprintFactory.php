<?php

namespace Kdjfs\LoginLegalLinks;

use Kirby\Cms\App;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

/**
 * Builds the field group `fields/login-legal-links` for the site blueprint.
 *
 * Registered as a callback, so Kirby builds it when the blueprint is loaded
 * (Cms/Blueprint.php, find()) – in the language of the logged-in user and with
 * the configuration of the current installation.
 */
final class BlueprintFactory
{
    /**
     * Characters Kirby would interpret in a help text: `{}` for query
     * templates, `()` for KirbyTags, `*_~` for Markdown emphasis and
     * strikethrough, the rest for other Markdown syntax. A colon is turned
     * into an entity because Markdown (Parsedown) autolinks bare `http(s)://`
     * addresses, which the escaped parentheses alone do not prevent. HTML
     * special characters are handled by Escape::html() before.
     */
    private const HELP_ENTITIES = [
        '{'  => '&#123;',
        '}'  => '&#125;',
        '('  => '&#40;',
        ')'  => '&#41;',
        '['  => '&#91;',
        ']'  => '&#93;',
        '*'  => '&#42;',
        '_'  => '&#95;',
        '`'  => '&#96;',
        '\\' => '&#92;',
        ':'  => '&#58;',
        '~'  => '&#126;',
    ];

    /**
     * Returns the field definition.
     *
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @return array Field definition as a blueprint would contain it
     */
    public static function fieldGroup(App $kirby): array
    {
        $definition = [
            'type'    => LegalLinks::FIELD_TYPE,
            'label'   => I18n::translate('kdjfs.login-legal-links.field.label'),
            'default' => [
                ['label' => I18n::translate('kdjfs.login-legal-links.default.imprint'), 'link' => ''],
                ['label' => I18n::translate('kdjfs.login-legal-links.default.privacy'), 'link' => ''],
                ['label' => I18n::translate('kdjfs.login-legal-links.default.accessibility'), 'link' => ''],
            ],
            'fields' => [
                'label' => [
                    'type'     => 'text',
                    'label'    => I18n::translate('kdjfs.login-legal-links.field.column.label'),
                    'required' => true,
                ],
                'link' => [
                    'type'    => 'link',
                    'label'   => I18n::translate('kdjfs.login-legal-links.field.column.link'),
                    'options' => ['page', 'url'],
                ],
            ],
        ];

        $help = static::help($kirby);

        if ($help !== null) {
            $definition['help'] = $help;
        }

        return $definition;
    }

    /**
     * Names the configured entries, or returns null if there are none.
     *
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @return string|null Help text with escaped values
     */
    private static function help(App $kirby): string|null
    {
        $entries = (new LegalLinks($kirby, I18n::locale()))->configuredEntries();

        if ($entries === []) {
            return null;
        }

        $summary = implode(', ', array_map(
            fn (array $entry) => static::escape($entry['label']) . ' → ' . static::escape($entry['link']),
            $entries
        ));

        return I18n::template('kdjfs.login-legal-links.field.help.configured', null, ['entries' => $summary]);
    }

    /**
     * Makes a configured value safe for Kirby's help pipeline.
     *
     * @param string $value Label or link from the configuration
     * @return string Value that renders as plain text
     */
    private static function escape(string $value): string
    {
        return strtr(Escape::html($value), self::HELP_ENTITIES);
    }
}
