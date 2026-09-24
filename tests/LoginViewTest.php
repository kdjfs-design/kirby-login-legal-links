<?php

namespace Tests;

use Kdjfs\LoginLegalLinks\LoginView;

class LoginViewTest extends PluginTestCase
{
    /**
     * A Kirby update could change what the core login action returns. Anything
     * without a props array has to pass through untouched – the plugin cannot
     * know where its props would belong.
     *
     * @return void
     */
    public function testViewWithoutPropsArrayPassesThroughUntouched(): void
    {
        $kirby = $this->kirby([
            'options' => ['kdjfs.login-legal-links' => ['links' => [['label' => 'Impressum', 'link' => 'impressum']]]],
        ]);

        foreach ([
            null,
            'k-login-view',
            ['component' => 'k-login-view'],
            ['component' => 'k-login-view', 'props' => 'kein Array'],
        ] as $view) {
            $this->assertSame($view, LoginView::extend($kirby, $view));
        }
    }

    /**
     * Only the server log learns why the links are missing, so the entry has
     * to name the kind of failure, not just its message. Malformed YAML with
     * Symfony's parser is a real way to make the resolution throw.
     *
     * @return void
     */
    public function testFailureIsLoggedWithExceptionClass(): void
    {
        $kirby = $this->kirby([
            'options'    => ['yaml.handler' => 'symfony'],
            'blueprints' => ['site' => ['fields' => ['legal' => ['type' => 'login-legal-links']]]],
            'site'       => ['content' => ['legal' => "a: [1, 2\nb: unterminated \"quote"]],
        ]);

        $logFile     = tempnam(sys_get_temp_dir(), 'legal-links-log');
        $previousLog = ini_set('error_log', $logFile);

        try {
            $view = ['component' => 'k-login-view', 'props' => ['methods' => ['password']]];

            $this->assertSame($view, LoginView::extend($kirby, $view));
            $this->assertStringContainsString(
                'login-legal-links: Symfony\Component\Yaml\Exception\ParseException: ',
                file_get_contents($logFile)
            );
        } finally {
            ini_set('error_log', $previousLog);
            unlink($logFile);
        }
    }

    public function testViewWithPropsGetsTheLegalLinks(): void
    {
        $kirby = $this->kirby([
            'options' => ['kdjfs.login-legal-links' => ['links' => [['label' => 'Impressum', 'link' => 'impressum']]]],
        ]);

        $view = LoginView::extend($kirby, ['component' => 'k-login-view', 'props' => ['methods' => ['password']]]);

        $this->assertSame(['password'], $view['props']['methods']);
        $this->assertSame([['label' => 'Impressum', 'url' => 'https://example.com/impressum']], $view['props']['legalLinks']);
        $this->assertFalse($view['props']['legalLinksNewTab']);
        $this->assertSame([], $view['props']['legalLinksWarnings']);
    }
}
