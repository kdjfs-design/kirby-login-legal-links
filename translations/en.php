<?php

return [
    'kdjfs.login-legal-links.nav'                   => 'Legal information',
    'kdjfs.login-legal-links.field.label'           => 'Links on the Panel login',
    'kdjfs.login-legal-links.field.column.label'    => 'Label',
    'kdjfs.login-legal-links.field.column.link'     => 'Link',
    'kdjfs.login-legal-links.field.help.configured' => 'The configuration sets: {{ entries }}. As soon as an entry here has a link, only this list applies.',
    'kdjfs.login-legal-links.default.imprint'       => 'Imprint',
    'kdjfs.login-legal-links.default.privacy'       => 'Privacy policy',
    'kdjfs.login-legal-links.default.accessibility' => 'Accessibility',
    'kdjfs.login-legal-links.warning.none'          => 'No legal links set, neither in the configuration nor in the Panel.',
    'kdjfs.login-legal-links.warning.config'        => 'The option kdjfs.login-legal-links.links is not a list and is ignored.',
    'kdjfs.login-legal-links.warning.entry'         => 'Entry {{ position }} is not a list of label and link and is skipped.',
    'kdjfs.login-legal-links.warning.label'         => 'Entry {{ position }} has no label and is skipped.',
    'kdjfs.login-legal-links.warning.link'          => 'Entry "{{ label }}" has no link and is skipped.',
    'kdjfs.login-legal-links.warning.page'          => 'Entry "{{ label }}" skipped – page "{{ link }}" not found.',
    'kdjfs.login-legal-links.warning.draft'         => 'Entry "{{ label }}" skipped – page "{{ link }}" is a draft.',
    'kdjfs.login-legal-links.warning.scheme'        => 'Entry "{{ label }}" skipped – only http and https links are allowed.',
];
