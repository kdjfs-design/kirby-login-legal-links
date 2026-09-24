<?php

return [
    'kdjfs.login-legal-links.nav'                   => 'Rechtliche Hinweise',
    'kdjfs.login-legal-links.field.label'           => 'Links im Panel-Login',
    'kdjfs.login-legal-links.field.column.label'    => 'Beschriftung',
    'kdjfs.login-legal-links.field.column.link'     => 'Link',
    'kdjfs.login-legal-links.field.help.configured' => 'In der Konfiguration hinterlegt: {{ entries }}. Sobald hier ein Eintrag einen Link hat, gilt nur diese Liste.',
    'kdjfs.login-legal-links.default.imprint'       => 'Impressum',
    'kdjfs.login-legal-links.default.privacy'       => 'Datenschutz',
    'kdjfs.login-legal-links.default.accessibility' => 'Barrierefreiheit',
    'kdjfs.login-legal-links.warning.none'          => 'Keine Rechtslinks gesetzt, weder in der Konfiguration noch im Panel.',
    'kdjfs.login-legal-links.warning.config'        => 'Die Option kdjfs.login-legal-links.links ist keine Liste und wird ignoriert.',
    'kdjfs.login-legal-links.warning.entry'         => 'Eintrag {{ position }} ist keine Liste aus Beschriftung und Link und wird übersprungen.',
    'kdjfs.login-legal-links.warning.label'         => 'Eintrag {{ position }} hat keine Beschriftung und wird übersprungen.',
    'kdjfs.login-legal-links.warning.link'          => 'Eintrag „{{ label }}” hat keinen Link und wird übersprungen.',
    'kdjfs.login-legal-links.warning.page'          => 'Eintrag „{{ label }}” übersprungen – Seite „{{ link }}” nicht gefunden.',
    'kdjfs.login-legal-links.warning.draft'         => 'Eintrag „{{ label }}” übersprungen – Seite „{{ link }}” ist ein Entwurf.',
    'kdjfs.login-legal-links.warning.scheme'        => 'Eintrag „{{ label }}” übersprungen – nur http- und https-Links sind erlaubt.',
];
