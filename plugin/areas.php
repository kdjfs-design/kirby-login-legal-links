<?php

use Kdjfs\LoginLegalLinks\LegalLinks;

/**
 * Extends the core login area. Kirby merges this into the core area with
 * array_replace_recursive (Cms/Loader.php, areas()), so only the view action
 * is replaced and everything else of the area stays.
 *
 * The action calls the original one and adds three props. The original comes
 * from $kirby->core()->area(), which loads the core areas without plugins
 * (Cms/Core.php, load()) – there is no recursion. Before login the Panel only
 * loads the login and logout areas (Panel/Panel.php, areas()); an own area
 * would not exist there, an extension of `login` does.
 */
return [
    'login' => fn ($kirby) => [
        'views' => [
            'login' => [
                'action' => function () use ($kirby) {
                    $view = $kirby->core()->area('login')['views']['login']['action']();

                    if (is_array($view) === false || is_array($view['props'] ?? null) === false) {
                        return $view;
                    }

                    try {
                        $legalLinks = new LegalLinks($kirby, $kirby->panelLanguage());
                        $resolution = $legalLinks->resolve();

                        $view['props']['legalLinks']         = $resolution['links'];
                        $view['props']['legalLinksNewTab']   = $legalLinks->newTab();
                        $view['props']['legalLinksWarnings'] = $kirby->option('debug') === true
                            ? $resolution['warnings']
                            : [];
                    } catch (Throwable $exception) {
                        // The login must stay usable; the view goes out as the core built it
                        error_log('login-legal-links: ' . $exception->getMessage());
                    }

                    return $view;
                },
            ],
        ],
    ],
];
