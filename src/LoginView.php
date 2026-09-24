<?php

namespace Kdjfs\LoginLegalLinks;

use Kirby\Cms\App;
use Throwable;

/**
 * Adds the legal links to the view the core login action returns.
 *
 * Kept apart from plugin/areas.php so the behaviour can be tested with any
 * view, not only with the one the installed Kirby version builds.
 */
final class LoginView
{
    /**
     * Adds the props `legalLinks`, `legalLinksNewTab` and
     * `legalLinksWarnings` (only with `debug`) to the view.
     *
     * The login must stay usable: a view without a props array passes through
     * untouched, and if resolving the links fails, the view goes out exactly
     * as it came in.
     *
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @param mixed $view Return value of the core login view action
     * @return mixed The view with the added props, or the view unchanged
     */
    public static function extend(App $kirby, mixed $view): mixed
    {
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
            // The view goes out as the core built it; the cause only reaches the server log
            error_log('login-legal-links: ' . $exception->getMessage());
        }

        return $view;
    }
}
