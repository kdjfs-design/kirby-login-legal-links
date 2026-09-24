<?php

/**
 * Field type for the list of login links. Identical to `structure` except for
 * the value of a field that was never saved.
 *
 * Kirby applies blueprint defaults only when a model is created
 * (Cms/ModelWithContent.php), and the site is never created – a `default` in
 * the site blueprint would stay without effect. The Panel fills a missing
 * content key through Field::emptyValue() (Form/Fields.php, reset()), which
 * asks `methods.emptyValue` first (Form/Field.php). Returning the default rows
 * there shows the presets until the field is saved; a list emptied on purpose
 * is stored as an empty value and stays empty.
 */
return [
    'login-legal-links' => [
        'extends' => 'structure',
        'methods' => [
            'emptyValue' => function () {
                return $this->rows($this->default);
            },
        ],
    ],
];
