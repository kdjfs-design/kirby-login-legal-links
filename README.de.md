# Login Legal Links

[![English version](https://img.shields.io/badge/Lang-EN-D2AA26?style=for-the-badge&labelColor=555555)](https://github.com/kdjfs-design/kirby-login-legal-links/blob/main/README.md)

[![Aktuelle Version](https://img.shields.io/packagist/v/kdjfs/login-legal-links?style=for-the-badge&labelColor=555555)](https://packagist.org/packages/kdjfs/login-legal-links)
[![Tests](https://img.shields.io/github/actions/workflow/status/kdjfs-design/kirby-login-legal-links/tests.yml?style=for-the-badge&labelColor=555555&label=Tests)](https://github.com/kdjfs-design/kirby-login-legal-links/actions/workflows/tests.yml)
[![Lizenz](https://img.shields.io/packagist/l/kdjfs/login-legal-links?style=for-the-badge&labelColor=555555)](LICENSE)

Zeigt unter dem Anmeldeformular des Kirby-Panels Links zum Impressum, zur
Datenschutzerklärung und zu beliebigen weiteren Seiten.

## Warum

Bei der Anmeldung im Panel werden personenbezogene Daten verarbeitet:
E-Mail-Adresse, Passwort, IP-Adresse und ein Sitzungscookie. Nach Art. 13 DSGVO
müssen die Datenschutzhinweise dort erreichbar sein, wo Daten erhoben werden,
und in Deutschland verlangt § 5 DDG, dass das Impressum von jeder Seite aus
unmittelbar erreichbar ist. Kirbys Anmeldeseite hat für diese Links keinen
Platz. Das Plugin schafft ihn.

Das ist keine Rechtsberatung. Das Plugin verlinkt vorhandene Seiten, es
schreibt sie nicht.

## Voraussetzungen

- Kirby 5 (ab 5.2)
- PHP 8.2 bis 8.5

## Installation

```bash
composer require kdjfs/login-legal-links
```

Oder das Repository herunterladen und nach `site/plugins/login-legal-links`
kopieren.

## Optionen

Die Links können aus der Konfiguration kommen, aus einem Feld im Panel oder aus
beidem.

### In der Konfiguration

```php
// site/config/config.php
return [
    'kdjfs.login-legal-links' => [
        'links' => [
            ['label' => 'Impressum', 'link' => 'impressum'],                // Seiten-ID
            ['label' => ['de' => 'Datenschutz', 'en' => 'Privacy'],          // je Panel-Sprache
             'link'  => 'page://2cBnWgsQKanNkNSd'],                          // Seiten-UUID
            ['label' => 'Barrierefreiheit', 'link' => 'https://example.com/barrierefreiheit'], // externe Adresse
        ],
        'newTab' => false,
    ],
];
```

| Option | Standard | Beschreibung |
|---|---|---|
| `links` | `[]` | Liste von Einträgen mit `label` und `link`. `label` ist ein Text oder ein Array aus Sprachkürzel → Text; verwendet wird die Panel-Sprache, sonst Englisch, sonst der erste Eintrag. `link` ist eine Seiten-ID, eine Seiten-UUID oder eine vollständige Adresse mit `http` oder `https`. |
| `newTab` | `false` | `true` öffnet alle Links in einem neuen Tab (`target="_blank" rel="noopener"`). Die Voreinstellung folgt der Empfehlung des W3C, keine neuen Fenster ungefragt zu öffnen. |

### Im Panel

Die Feldgruppe in den Site-Blueprint aufnehmen. Den Feldnamen bestimmst du
selbst:

```yaml
# site/blueprints/site.yml
fields:
  loginLegalLinks: fields/login-legal-links
```

Redakteure pflegen dann eine Liste aus Beschriftung und Link (Seite oder
Adresse). Solange das Feld noch nie gespeichert wurde, enthält es drei
Vorgaben – Impressum, Datenschutz, Barrierefreiheit –, bei denen nur noch der
Link fehlt.

### Welche Liste gilt

Sobald ein Eintrag der Panel-Liste einen Link hat, gilt **nur** die
Panel-Liste, und die Konfiguration bleibt vollständig unbeachtet. Andernfalls
gilt die Konfiguration. Gibt es beides, weist das Feld im Panel in seinem
Hilfetext darauf hin und nennt die Einträge aus der Konfiguration.

## Was angezeigt wird

- Die Links stehen in jedem Schritt der Anmeldung unter dem Anmeldedialog:
  Passwort, Anmeldecode, Code der Zwei-Faktor-Anmeldung und Zurücksetzen des
  Passworts.
- Einträge ohne Beschriftung oder Link, nicht vorhandene Seiten, Entwürfe und
  Links mit einem anderen Schema als `http` oder `https` werden weggelassen.
- Bleibt nichts übrig, sieht die Anmeldung genauso aus wie ohne das Plugin.
- Bei eingeschaltetem `debug` meldet die Browserkonsole jeden weggelassenen
  Eintrag und eine Anmeldeseite ganz ohne Links, jeweils mit dem Präfix
  `login-legal-links:`.

## Sprache

Beschriftungen und Seitenadressen richten sich nach der Sprache der
Panel-Anmeldung: `panel.language`, wenn gesetzt, sonst die Standardsprache
einer mehrsprachigen Website, sonst Englisch. Auf einer mehrsprachigen Website
wird die Liste aus der Sprache mit demselben Kürzel gelesen, ersatzweise aus
der Standardsprache. Die Texte im Panel liegen auf Englisch und Deutsch bei.

## Grenzen

- Die Installationsseite des Panels zeigt keine Links.
- Die Browserkonsole zeigt `Plugin is replacing "k-login-view"`. Diesen Hinweis
  gibt Kirby bei jedem Plugin aus, das eine mitgelieferte Ansicht des Panels
  erweitert.
- Ersetzt ein weiteres Plugin ebenfalls `k-login-view`, kommen sich beide in
  die Quere; es gilt das zuletzt geladene.
- Ändert ein Kirby-Update die Anmeldeansicht, können die Links verschwinden.
  Die Anmeldung selbst funktioniert weiter.

## Die Arbeit unterstützen

Wenn dir das Plugin Arbeit erspart, kannst du mir gern
[einen Kaffee ausgeben](https://buymeacoffee.com/janstieler) oder direkt etwas
per [PayPal](https://paypal.me/kdjfs) beisteuern.

## Aufbau

Wie sich das Plugin in das Panel einfügt und warum es so gebaut ist, steht in
[ARCHITECTURE.md](ARCHITECTURE.md) – nur auf Englisch, weil sich das an
Mitentwickelnde richtet.

## Lizenz

MIT — siehe [LICENSE](LICENSE).
