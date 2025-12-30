<?php

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $config = $GLOBALS['app_config'] ?? [];
        $baseUrl = rtrim($config['app']['base_url'] ?? '/public', '/');

        $data = array_merge([
            'baseUrl' => $baseUrl
        ], $data);

        extract($data);
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }
}