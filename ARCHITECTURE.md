# Architecture

How the plugin gets links onto a Panel view that has no place for them, and why
it is built this way. For usage see the [README](README.md).

## The path of a link

1. `/panel/login` is requested. The Panel resolves the view `login` of the area
   `login`. The plugin extends that area; Kirby merges the extension into the
   core area with `array_replace_recursive` (`kirby/src/Cms/Loader.php`,
   `areas()`), so only the view action is replaced.
2. The action (`plugin/areas.php`) calls the original action from
   `$kirby->core()->area('login')`. That loads the core areas without plugins
   (`kirby/src/Cms/Core.php`, `load()`), so there is no recursion.
3. `LegalLinks::resolve()` (`src/LegalLinks.php`) picks the source: the first
   field of type `login-legal-links` in the site blueprint if one of its rows
   has a link, otherwise the option `links`. Each entry is resolved into
   label and absolute URL or dropped with a warning.
4. The action adds `legalLinks`, `legalLinksNewTab` and `legalLinksWarnings`
   (the latter only with `debug`) to the props of the original view.
5. In the Panel, the wrapped `k-login-view` (`index.js`) calls the inherited
   render function and appends a `<nav>` to the slot children of its root
   component `k-panel-outside`.

## Files

| File | Responsibility |
|---|---|
| `index.php` | Registration only |
| `plugin/areas.php` | Wrapper around the login view action |
| `plugin/fields.php` | Field type `login-legal-links` |
| `src/LegalLinks.php` | Source selection, resolution, validation, warnings |
| `src/BlueprintFactory.php` | Field group `fields/login-legal-links` with presets and help |
| `index.js` | Wrapper around `k-login-view`, field component |
| `index.css` | Link list |
| `translations/` | Panel texts and warnings |

## Constraints the Panel imposes

- **No extension point on the login.** `k-login-view` renders a fixed
  template (`kirby/panel/dist/js/index.min.js`, search for `k-login-view`).
  The only ways in are replacing the component or injecting into the DOM.
- **No plugin areas before login.** Without a user, the Panel only loads the
  areas `login` and `logout` (`kirby/src/Panel/Panel.php`, `areas()`). Data
  for the login therefore has to come through the login area itself.
- **`extends` becomes a constructor.** Kirby replaces a string in `extends` with
  `components[name].extend(…)`, so the inherited render function lives at
  `this.$options.extends.options.render`.
- **Defaults do not apply to the site.** Kirby applies blueprint defaults only
  when a model is created (`kirby/src/Cms/ModelWithContent.php`); the site is
  never created. The Panel fills a missing content key through
  `Field::emptyValue()` (`kirby/src/Form/Fields.php`, `reset()`), which asks
  `methods.emptyValue` first (`kirby/src/Form/Field.php`). The field type
  returns its default rows there.
- **Unknown field types turn into `info`.** A blueprint field with an
  unregistered type is replaced by an info field. The field type has to be
  registered for `LegalLinks` to find the field by its type.
- **Help texts are templates.** A field's help runs through `toSafeString()`
  (query templates) and `kirbytext()` (KirbyTags, Markdown, HTML) before the
  Panel renders it as HTML (`kirby/src/Form/Field.php`, computed `help`).
  Configured values are therefore HTML-escaped first, and then each of these
  characters is turned into a numeric entity (`BlueprintFactory::escape()`,
  list in `BlueprintFactory::HELP_ENTITIES`):
  `{` `}` for query templates, `(` `)` for KirbyTags, `[` `]` `*` `_` `` ` ``
  `\` for Markdown, `~` for Markdown strikethrough, and `:` because Markdown
  (Parsedown) turns a bare `http://` or `https://` address into a link, which
  the escaped parentheses alone do not prevent.

## Decisions

- **Wrap, do not copy.** Both the server action and the render function call
  the original and add to it. Copying the core template would silently freeze
  it at one Kirby version.
- **Data with the view, no API route.** A route without authentication would
  mean an extra request, flickering links and one more public endpoint for
  data that the view can carry itself.
- **Panel list replaces the configuration as a whole.** Merging entry by entry
  would need a key; the label is none, because renaming it would duplicate the
  link.
- **Rows without link do not count.** The presets carry labels only; otherwise
  saving the site once would silently switch off the configuration.
- **Field found by type.** Developers name the field as they like.
- **Drop, never throw.** Every problem with an entry drops that entry. The
  whole resolution runs inside a `try` in the area action; on failure the view
  goes out exactly as the core built it.
- **Only http and https.** Anything else, including `javascript:` in any
  spelling and protocol-relative URLs, is dropped.
- **Text colour for the links.** `--color-text-dimmed` falls below 4.5:1
  contrast on the Panel background. `--color-text` reaches about 18:1 in the
  light and 16:1 in the dark theme, measured against the background of
  `<html>` – the Panel's `body` is transparent.

## Verifying locally

### The test suite

```bash
cd site/plugins/login-legal-links
../../../vendor/bin/phpunit      # inside a Kirby project
composer install && composer test  # standalone repository
```

`AreaTest::testCoreLoginActionStillHasTheExpectedShape` is the early warning
for Kirby updates: if it fails, read `kirby/config/areas/login.php` first.

`AreaTest::testBrokenResolutionLeavesTheViewUntouched` makes the resolution
fail with malformed YAML in the site field and `yaml.handler => symfony`. A
site blueprint that throws would not do: requesting one core area resolves all
of them, so it would already break `core()->area('login')` outside the
plugin's `try`.

### What the suite cannot answer

Rendering in the Panel. Start a local server **without OPcache** – the CLI
server otherwise serves PHP files up to 60 seconds old:

```bash
php -d opcache.enable_cli=0 -S localhost:8765 kirby/router.php
```

Then check `/panel/login` in light and dark mode, with the login code form
(`'auth' => ['methods' => ['code']]`), with `newTab`, with nothing set, and
the presets in the site field of a site that never saved it.
