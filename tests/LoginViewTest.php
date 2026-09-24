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
