<?php

use Kdjfs\LoginLegalLinks\LoginView;

/**
 * Extends the core login area. Kirby merges this into the core area with
 * array_replace_recursive (Cms/Loader.php, areas()), so only the view action
 * is replaced and everything else of the area stays.
 *
 * The action calls the original one and hands its result to LoginView, which
 * adds the props. The original comes from $kirby->core()->area(), which loads
 * the core areas without plugins (Cms/Core.php, load()) – there is no
 * recursion. Before login the Panel only loads the login and logout areas
 * (Panel/Panel.php, areas()); an own area would not exist there, an extension
 * of `login` does.
 */
return [
    'login' => fn ($kirby) => [
        'views' => [
            'login' => [
                'action' => fn () => LoginView::extend(
                    $kirby,
                    $kirby->core()->area('login')['views']['login']['action']()
                ),
            ],
        ],
    ],
];
