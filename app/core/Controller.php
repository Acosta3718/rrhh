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

    protected function buildPagination(int $page, int $perPage, int $total, array $params = []): array
    {
        $totalPages = (int) max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));

        return [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'params' => array_filter(
                $params,
                fn($value) => $value !== null && $value !== ''
            )
        ];
    }
}