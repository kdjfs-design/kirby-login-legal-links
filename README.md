# Login Legal Links

[![Deutsche Fassung](https://img.shields.io/badge/Lang-DE-D2AA26?style=for-the-badge&labelColor=555555)](https://github.com/kdjfs-design/kirby-login-legal-links/blob/main/README.de.md)

[![Latest version](https://img.shields.io/packagist/v/kdjfs/login-legal-links?style=for-the-badge&labelColor=555555)](https://packagist.org/packages/kdjfs/login-legal-links)
[![Tests](https://img.shields.io/github/actions/workflow/status/kdjfs-design/kirby-login-legal-links/tests.yml?style=for-the-badge&labelColor=555555&label=Tests)](https://github.com/kdjfs-design/kirby-login-legal-links/actions/workflows/tests.yml)
[![License](https://img.shields.io/packagist/l/kdjfs/login-legal-links?style=for-the-badge&labelColor=555555)](LICENSE)

Adds links to the imprint, the privacy policy and any other page below the
login form of the Kirby Panel.

## Why

The Panel login processes personal data: email address, password, IP address
and a session cookie. Under Art. 13 GDPR the privacy information has to be
available where data is collected, and in Germany § 5 DDG requires the imprint
to be directly reachable from every page. Kirby's login has no place for these
links. This plugin adds one.

This is not legal advice. The plugin links pages you already have; it does not
write them.

## Requirements

- Kirby 5 (5.2 or later)
- PHP 8.2 to 8.5

## Installation

```bash
composer require kdjfs/login-legal-links
```

Or download the repository and copy it to `site/plugins/login-legal-links`.

## Options

Links can come from the configuration, from a field in the Panel, or both.

### In the configuration

```php
// site/config/config.php
return [
    'kdjfs.login-legal-links' => [
        'links' => [
            ['label' => 'Imprint', 'link' => 'imprint'],                    // page id
            ['label' => ['en' => 'Privacy', 'de' => 'Datenschutz'],          // per Panel language
             'link'  => 'page://2cBnWgsQKanNkNSd'],                          // page UUID
            ['label' => 'Accessibility', 'link' => 'https://example.com/a11y'], // external URL
        ],
        'newTab' => false,
    ],
];
```

| Option | Default | Description |
|---|---|---|
| `links` | `[]` | List of entries with `label` and `link`. `label` is a string or an array of language code → text; the Panel language is used, then English, then the first entry. `link` is a page id, a page UUID or an absolute `http`/`https` URL. |
| `newTab` | `false` | `true` opens all links in a new tab (`target="_blank" rel="noopener"`). The default follows the W3C recommendation not to open new windows unasked. |

### In the Panel

Add the field group to your site blueprint. The field name is up to you:

```yaml
# site/blueprints/site.yml
fields:
  loginLegalLinks: fields/login-legal-links
```

Editors then maintain a list of label and link (page or URL). Until the field
is saved for the first time it shows three presets – Imprint, Privacy policy,
Accessibility – that only need their links.

### Which list applies

As soon as one entry of the Panel list has a link, **only** the Panel list
applies and the configuration is ignored as a whole. Otherwise the
configuration applies. If both exist, the Panel field says so in its help text
and names the configured entries.

## What is shown

- The links appear below the login dialog in every state of the login:
  password, login code, two-factor code and password reset.
- Entries without a label or link, pages that do not exist, drafts and links
  with a scheme other than `http`/`https` are left out.
- If nothing is left, the login looks exactly as without the plugin.
- With `debug` enabled, the browser console reports every left-out entry, and
  a login without any links, each prefixed with `login-legal-links:`.

## Language

Labels and page URLs follow the language of the Panel login: `panel.language`
if set, otherwise the default language of a multi-language site, otherwise
English. On a multi-language site, the list is read from the site language
with the same code, falling back to the default language. The plugin ships
English and German texts for the Panel.

## Limitations

- The Panel installation screen shows no links.
- The browser console shows `Plugin is replacing "k-login-view"`. That is
  Kirby's regular notice for plugins that extend a core view.
- Another plugin that replaces `k-login-view` as well will conflict; the one
  loaded last wins.
- A Kirby update that changes the login view may hide the links. The login
  itself keeps working.
- With `debug` enabled, the reports about left-out entries reach every
  visitor of the login page, signed in or not, including the configured link
  values. Keep `debug` off on publicly reachable sites.
- Everyone who may edit the site in the Panel decides which links appear on
  the login page – including external addresses. If editors should not be
  able to change them, leave the field group out of the site blueprint and
  set the links in the configuration only.

## Support the work

If this plugin saves you time, you can
[buy me a coffee](https://buymeacoffee.com/janstieler) or send something via
[PayPal](https://paypal.me/kdjfs).

## Internals

How the plugin hooks into the Panel, and why:
[ARCHITECTURE.md](https://github.com/kdjfs-design/kirby-login-legal-links/blob/main/ARCHITECTURE.md).

## Licence

MIT — see [LICENSE](LICENSE).
