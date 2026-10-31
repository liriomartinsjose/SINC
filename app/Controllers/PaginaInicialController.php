<?php

declare(strict_types=1);

final class PaginaInicialController extends Controller
{
    public function index(): void
    {
        $this->render(
            'public/inicio',
            [
                'pageTitle' => 'Uma Angola melhor começa connosco',
                'pageStyles' => [
                    'public/assets/css/pagina-inicial.css',
                ],
                'pageScripts' => [
                    'public/assets/js/pagina-inicial.js',
                ],
            ],
            'public'
        );
    }
}
